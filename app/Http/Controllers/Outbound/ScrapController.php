<?php

namespace App\Http\Controllers\Outbound;

use App\Enums\JenisKeluar;
use App\Enums\StatusDokumen;
use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Outbound\Concerns\MenulisDetailPengeluaran;
use App\Http\Requests\Outbound\StoreScrapRequest;
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
 * Pengeluaran DTY scrap/reject (PRD §7.3) — F3C.
 *
 * Catatan WAJIB (tujuan/penerima). DRAFT -> POSTED, mengurangi stok.
 */
class ScrapController extends Controller
{
    use MenulisDetailPengeluaran;

    private const JENIS = JenisKeluar::DtyScrapReject;

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
                    ->orWhere('catatan', 'like', '%'.$filter['cari'].'%');
            }))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('outbound.scrap.index', [
            'daftar' => $daftar,
            'filter' => $filter,
            'daftarStatus' => $this->daftarStatus(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', [TransaksiKeluar::class, self::JENIS]);

        return view('outbound.scrap.create', array_merge($this->dataMasterForm('DTY', true), [
            'nomorPratinjau' => $this->nomor->peek('TRX_KELUAR'),
            'detailAwal' => old('detail', [$this->barisKosong()]),
            'payloadJs' => $this->payloadJs('DTY', true),
        ]));
    }

    public function store(StoreScrapRequest $request): RedirectResponse
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
            "Buat draf pengeluaran scrap {$transaksi->nomor_transaksi} ({$transaksi->detail()->count()} baris).",
        );

        return redirect()
            ->route('outbound.scrap.show', $transaksi)
            ->with('sukses', "Draf pengeluaran scrap {$transaksi->nomor_transaksi} tersimpan. Stok BELUM berubah — tekan \"Posting\" untuk memproses.");
    }

    public function show(TransaksiKeluar $scrap): View
    {
        Gate::authorize('view', [$scrap, self::JENIS]);

        $scrap->load(['detail.barang.kategori', 'detail.grade', 'detail.satuanKemasan', 'pembuat', 'pemvoid']);

        return view('outbound.scrap.show', [
            'transaksi' => $scrap,
            'totalNetto' => PengeluaranService::totalNetto($scrap),
        ]);
    }

    public function edit(TransaksiKeluar $scrap): View
    {
        Gate::authorize('update', [$scrap, self::JENIS]);

        $detail = old('detail', $this->detailKeArray($scrap)->all());

        return view('outbound.scrap.edit', array_merge($this->dataMasterForm('DTY', true), [
            'transaksi' => $scrap,
            'detailAwal' => $detail === [] ? [$this->barisKosong()] : $detail,
            'payloadJs' => $this->payloadJs('DTY', true),
        ]));
    }

    public function update(StoreScrapRequest $request, TransaksiKeluar $scrap): RedirectResponse
    {
        Gate::authorize('update', [$scrap, self::JENIS]);

        try {
            DB::transaction(function () use ($request, $scrap): void {
                $scrap->update($this->headerDari($request->validated()));
                $this->tulisDetail($scrap, $request->validated('detail'));
            });
        } catch (AturanBisnisException $e) {
            return back()->withInput()->withErrors(['detail' => $e->getMessage()]);
        }

        ActivityLog::catat(
            'UPDATE',
            PengeluaranService::modul(self::JENIS),
            (int) $scrap->id,
            "Ubah draf pengeluaran scrap {$scrap->nomor_transaksi}.",
        );

        return redirect()
            ->route('outbound.scrap.show', $scrap)
            ->with('sukses', "Draf pengeluaran scrap {$scrap->nomor_transaksi} diperbarui.");
    }

    public function destroy(TransaksiKeluar $scrap): RedirectResponse
    {
        Gate::authorize('delete', [$scrap, self::JENIS]);

        $nomor = $scrap->nomor_transaksi;
        $id = (int) $scrap->id;

        DB::transaction(function () use ($scrap): void {
            $scrap->detail()->delete();
            $scrap->delete();
        });

        ActivityLog::catat(
            'DELETE',
            PengeluaranService::modul(self::JENIS),
            $id,
            "Hapus draf pengeluaran scrap {$nomor}.",
        );

        return redirect()
            ->route('outbound.scrap.index')
            ->with('sukses', "Draf pengeluaran scrap {$nomor} dihapus.");
    }

    public function posting(TransaksiKeluar $scrap): RedirectResponse
    {
        Gate::authorize('posting', [$scrap, self::JENIS]);

        try {
            $this->pengeluaran->posting($scrap, request()->user()?->id);
        } catch (AturanBisnisException $e) {
            return back()->withErrors(['posting' => $e->getMessage()]);
        }

        return redirect()
            ->route('outbound.scrap.show', $scrap)
            ->with('sukses', "Pengeluaran scrap {$scrap->nomor_transaksi} berhasil DIPOSTING — stok berkurang.");
    }

    public function void(Request $request, TransaksiKeluar $scrap): RedirectResponse
    {
        Gate::authorize('void', [$scrap, self::JENIS]);

        $data = $request->validate([
            'alasan_void' => ['required', 'string', 'min:'.VoidTransaksiService::MIN_ALASAN, 'max:1000'],
        ]);

        try {
            $this->voidService->voidTransaksiKeluar($scrap, $data['alasan_void'], $request->user()->id);
        } catch (AturanBisnisException $e) {
            return back()->withErrors(['alasan_void' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('outbound.scrap.show', $scrap)
            ->with('sukses', "Pengeluaran scrap {$scrap->nomor_transaksi} DIBATALKAN (void) — stok dikembalikan.");
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function headerDari(array $data): array
    {
        return [
            'tanggal' => $data['tanggal'],
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
