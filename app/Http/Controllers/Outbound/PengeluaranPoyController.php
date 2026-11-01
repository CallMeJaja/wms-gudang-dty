<?php

namespace App\Http\Controllers\Outbound;

use App\Enums\JenisKeluar;
use App\Enums\StatusDokumen;
use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Outbound\Concerns\MenulisDetailPengeluaran;
use App\Http\Requests\Outbound\StorePengeluaranPoyRequest;
use App\Models\ActivityLog;
use App\Models\TransaksiKeluar;
use App\Services\NomorDokumenService;
use App\Services\PengeluaranService;
use App\Services\VoidTransaksiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Pengeluaran POY ke lantai produksi (PRD §4.3) — F3A.
 *
 * Alur: buat DRAFT -> tombol "Posting" -> POSTED.
 * Pengurangan stok HANYA saat POSTED; stok aktif tidak cukup -> tolak
 * dengan pesan A3 (buffer tidak boleh terpakai).
 */
class PengeluaranPoyController extends Controller
{
    use MenulisDetailPengeluaran;

    private const JENIS = JenisKeluar::PoyProduksi;

    public function __construct(
        private readonly PengeluaranService $pengeluaran,
        private readonly NomorDokumenService $nomor,
        private readonly VoidTransaksiService $voidService,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', [TransaksiKeluar::class, self::JENIS]);

        $filter = $this->filterIndex($request, self::JENIS->value);

        $daftar = TransaksiKeluar::query()
            ->with(['pembuat'])
            ->withSum('detail as total_netto', 'berat_netto_kg')
            ->where('jenis_keluar', self::JENIS)
            ->when($filter['dari'], fn ($q, $dari) => $q->whereDate('tanggal', '>=', $dari))
            ->when($filter['sampai'], fn ($q, $sampai) => $q->whereDate('tanggal', '<=', $sampai))
            ->when($filter['status'], fn ($q, $status) => $q->where('status', $status))
            ->when($filter['cari'] !== '', fn ($q) => $q->where(function ($q) use ($filter) {
                $q->where('nomor_transaksi', 'like', '%'.$filter['cari'].'%')
                    ->orWhere('operator_nama', 'like', '%'.$filter['cari'].'%');
            }))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('outbound.poy.index', [
            'daftar' => $daftar,
            'filter' => $filter,
            'daftarStatus' => $this->daftarStatus(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', [TransaksiKeluar::class, self::JENIS]);

        return view('outbound.poy.create', array_merge($this->dataMasterForm('POY', true), [
            'nomorPratinjau' => $this->nomor->peek('TRX_KELUAR'),
            'detailAwal' => old('detail', [$this->barisKosong()]),
            'payloadJs' => $this->payloadJs('POY', true),
        ]));
    }

    public function store(StorePengeluaranPoyRequest $request): RedirectResponse
    {
        Gate::authorize('create', [TransaksiKeluar::class, self::JENIS]);

        try {
            $transaksi = DB::transaction(function () use ($request): TransaksiKeluar {
                $transaksi = TransaksiKeluar::create(array_merge(
                    $this->headerDari($request->validated()),
                    [
                        'nomor_transaksi' => $this->nomorTransaksiBaru($this->nomor),
                        'jenis_keluar' => self::JENIS,
                        'status' => StatusDokumen::Draft,
                        'created_by' => $request->user()->id,
                    ],
                ));

                $this->tulisDetail($transaksi, $request->validated('detail'));

                return $transaksi;
            });
        } catch (AturanBisnisException $e) {
            return back()->withInput()->withErrors(['detail' => $e->getMessage()]);
        }

        ActivityLog::catat(
            'CREATE',
            PengeluaranService::modul(self::JENIS),
            (int) $transaksi->id,
            "Buat draf pengeluaran POY {$transaksi->nomor_transaksi} ({$transaksi->detail()->count()} baris).",
        );

        return redirect()
            ->route('outbound.poy.show', $transaksi)
            ->with('sukses', "Draf pengeluaran POY {$transaksi->nomor_transaksi} tersimpan. Stok BELUM berubah — tekan \"Posting\" untuk memproses.");
    }

    public function show(TransaksiKeluar $poy): View
    {
        Gate::authorize('view', [$poy, self::JENIS]);

        $poy->load(['detail.barang.kategori', 'detail.grade', 'detail.satuanKemasan', 'pembuat', 'pemvoid']);

        return view('outbound.poy.show', [
            'transaksi' => $poy,
            'totalNetto' => PengeluaranService::totalNetto($poy),
        ]);
    }

    public function edit(TransaksiKeluar $poy): View
    {
        Gate::authorize('update', [$poy, self::JENIS]);

        $detail = old('detail', $this->detailKeArray($poy)->all());

        return view('outbound.poy.edit', array_merge($this->dataMasterForm('POY', true), [
            'transaksi' => $poy,
            'detailAwal' => $detail === [] ? [$this->barisKosong()] : $detail,
            'payloadJs' => $this->payloadJs('POY', true),
        ]));
    }

    public function update(StorePengeluaranPoyRequest $request, TransaksiKeluar $poy): RedirectResponse
    {
        Gate::authorize('update', [$poy, self::JENIS]);

        try {
            DB::transaction(function () use ($request, $poy): void {
                $poy->update($this->headerDari($request->validated()));
                $this->tulisDetail($poy, $request->validated('detail'));
            });
        } catch (AturanBisnisException $e) {
            return back()->withInput()->withErrors(['detail' => $e->getMessage()]);
        }

        ActivityLog::catat(
            'UPDATE',
            PengeluaranService::modul(self::JENIS),
            (int) $poy->id,
            "Ubah draf pengeluaran POY {$poy->nomor_transaksi}.",
        );

        return redirect()
            ->route('outbound.poy.show', $poy)
            ->with('sukses', "Draf pengeluaran POY {$poy->nomor_transaksi} diperbarui.");
    }

    public function destroy(TransaksiKeluar $poy): RedirectResponse
    {
        Gate::authorize('delete', [$poy, self::JENIS]);

        $nomor = $poy->nomor_transaksi;
        $id = (int) $poy->id;

        DB::transaction(function () use ($poy): void {
            $poy->detail()->delete();
            $poy->delete();
        });

        ActivityLog::catat(
            'DELETE',
            PengeluaranService::modul(self::JENIS),
            $id,
            "Hapus draf pengeluaran POY {$nomor}.",
        );

        return redirect()
            ->route('outbound.poy.index')
            ->with('sukses', "Draf pengeluaran POY {$nomor} dihapus.");
    }

    public function posting(TransaksiKeluar $poy): RedirectResponse
    {
        Gate::authorize('posting', [$poy, self::JENIS]);

        try {
            $this->pengeluaran->posting($poy, request()->user()?->id);
        } catch (AturanBisnisException $e) {
            return back()->withErrors(['posting' => $e->getMessage()]);
        }

        return redirect()
            ->route('outbound.poy.show', $poy)
            ->with('sukses', "Pengeluaran POY {$poy->nomor_transaksi} berhasil DIPOSTING — stok aktif berkurang.");
    }

    public function void(Request $request, TransaksiKeluar $poy): RedirectResponse
    {
        Gate::authorize('void', [$poy, self::JENIS]);

        $data = $request->validate([
            'alasan_void' => ['required', 'string', 'min:'.VoidTransaksiService::MIN_ALASAN, 'max:1000'],
        ]);

        try {
            $this->voidService->voidTransaksiKeluar($poy, $data['alasan_void'], $request->user()->id);
        } catch (AturanBisnisException $e) {
            return back()->withErrors(['alasan_void' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('outbound.poy.show', $poy)
            ->with('sukses', "Pengeluaran POY {$poy->nomor_transaksi} DIBATALKAN (void) — stok dikembalikan.");
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function headerDari(array $data): array
    {
        return [
            'tanggal' => $data['tanggal'],
            'shift' => $data['shift'] ?? null,
            'operator_nama' => $data['operator_nama'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function barisKosong(): array
    {
        return [
            'barang_id' => null,
            'merge' => '',
            'grade_id' => null,
            'jumlah_kemasan' => '',
            'satuan_kemasan_id' => null,
            'jumlah_bobbin' => '',
            'berat_netto_kg' => '',
            'berat_gross_kg' => '',
            'keterangan' => '',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function daftarStatus(): array
    {
        return [
            StatusDokumen::Draft->value => StatusDokumen::Draft->label(),
            StatusDokumen::Posted->value => StatusDokumen::Posted->label(),
            StatusDokumen::Void->value => StatusDokumen::Void->label(),
        ];
    }
}
