<?php

namespace App\Http\Controllers\Inbound;

use App\Enums\JenisMasuk;
use App\Enums\StatusDokumen;
use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Inbound\Concerns\MenulisDetailPenerimaan;
use App\Http\Requests\Inbound\StorePenerimaanPoyRequest;
use App\Models\ActivityLog;
use App\Models\TransaksiMasuk;
use App\Services\KonversiSatuanService;
use App\Services\NomorDokumenService;
use App\Services\PenerimaanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Penerimaan POY dari IPCI (PRD §4.1) — F2B.
 *
 * Alur: buat DRAFT -> tombol "Posting" -> POSTED.
 * Stok & buffer HANYA berubah saat POSTED (lihat PenerimaanService::posting()).
 */
class PenerimaanPoyController extends Controller
{
    use MenulisDetailPenerimaan;

    private const JENIS = JenisMasuk::PoyIpci;

    public function __construct(
        private readonly PenerimaanService $penerimaan,
        private readonly NomorDokumenService $nomor,
        private readonly KonversiSatuanService $konversi,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', [TransaksiMasuk::class, self::JENIS]);

        $filter = $this->filterIndex($request, self::JENIS->value);

        $daftar = TransaksiMasuk::query()
            ->with(['partner', 'pembuat'])
            ->withSum('detail as total_kg', 'berat_kg')
            ->where('jenis_masuk', self::JENIS)
            ->when($filter['dari'], fn ($q, $dari) => $q->whereDate('tanggal', '>=', $dari))
            ->when($filter['sampai'], fn ($q, $sampai) => $q->whereDate('tanggal', '<=', $sampai))
            ->when($filter['status'], fn ($q, $status) => $q->where('status', $status))
            ->when($filter['cari'] !== '', fn ($q) => $q->where(function ($q) use ($filter) {
                $q->where('nomor_transaksi', 'like', '%'.$filter['cari'].'%')
                    ->orWhere('ref_dokumen', 'like', '%'.$filter['cari'].'%')
                    ->orWhere('operator_nama', 'like', '%'.$filter['cari'].'%');
            }))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('inbound.poy.index', [
            'daftar' => $daftar,
            'filter' => $filter,
            'daftarStatus' => $this->daftarStatus(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', [TransaksiMasuk::class, self::JENIS]);

        return view('inbound.poy.create', array_merge($this->dataMasterForm('POY'), [
            'nomorPratinjau' => $this->nomor->peek('TRX_MASUK'),
            'detailAwal' => old('detail', [$this->barisKosong()]),
            'payloadJs' => $this->payloadJs('POY'),
        ]));
    }

    public function store(StorePenerimaanPoyRequest $request): RedirectResponse
    {
        Gate::authorize('create', [TransaksiMasuk::class, self::JENIS]);

        try {
            $transaksi = DB::transaction(function () use ($request): TransaksiMasuk {
                $transaksi = TransaksiMasuk::create(array_merge(
                    $this->headerDari($request->validated()),
                    [
                        'nomor_transaksi' => $this->nomorTransaksiBaru($this->nomor),
                        'jenis_masuk' => self::JENIS,
                        'status' => StatusDokumen::Draft,
                        'created_by' => $request->user()->id,
                    ],
                ));

                $this->tulisDetail($transaksi, $request->validated('detail'), $this->konversi);

                return $transaksi;
            });
        } catch (AturanBisnisException $e) {
            return back()->withInput()->withErrors(['detail' => $e->getMessage()]);
        }

        ActivityLog::catat(
            'CREATE',
            PenerimaanService::modul(self::JENIS),
            (int) $transaksi->id,
            "Buat draf penerimaan POY {$transaksi->nomor_transaksi} ({$transaksi->detail()->count()} baris).",
        );

        return redirect()
            ->route('inbound.poy.show', $transaksi)
            ->with('sukses', "Draf penerimaan POY {$transaksi->nomor_transaksi} tersimpan. Stok BELUM berubah — tekan \"Posting\" untuk memproses.");
    }

    public function show(TransaksiMasuk $poy): View
    {
        Gate::authorize('view', [$poy, self::JENIS]);

        $poy->load(['detail.barang.kategori', 'detail.grade', 'detail.satuanKemasan', 'partner', 'pembuat', 'alokasiBuffer']);

        return view('inbound.poy.show', [
            'transaksi' => $poy,
            'totalKg' => PenerimaanService::totalKg($poy),
        ]);
    }

    public function edit(TransaksiMasuk $poy): View|RedirectResponse
    {
        Gate::authorize('update', [$poy, self::JENIS]);

        $detail = old('detail', $this->detailKeArray($poy)->all());

        return view('inbound.poy.edit', array_merge($this->dataMasterForm('POY'), [
            'transaksi' => $poy,
            'detailAwal' => $detail === [] ? [$this->barisKosong()] : $detail,
            'payloadJs' => $this->payloadJs('POY'),
        ]));
    }

    public function update(StorePenerimaanPoyRequest $request, TransaksiMasuk $poy): RedirectResponse
    {
        Gate::authorize('update', [$poy, self::JENIS]);

        try {
            DB::transaction(function () use ($request, $poy): void {
                $poy->update($this->headerDari($request->validated()));
                $this->tulisDetail($poy, $request->validated('detail'), $this->konversi);
            });
        } catch (AturanBisnisException $e) {
            return back()->withInput()->withErrors(['detail' => $e->getMessage()]);
        }

        ActivityLog::catat(
            'UPDATE',
            PenerimaanService::modul(self::JENIS),
            (int) $poy->id,
            "Ubah draf penerimaan POY {$poy->nomor_transaksi}.",
        );

        return redirect()
            ->route('inbound.poy.show', $poy)
            ->with('sukses', "Draf penerimaan POY {$poy->nomor_transaksi} diperbarui.");
    }

    public function destroy(TransaksiMasuk $poy): RedirectResponse
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
            PenerimaanService::modul(self::JENIS),
            $id,
            "Hapus draf penerimaan POY {$nomor}.",
        );

        return redirect()
            ->route('inbound.poy.index')
            ->with('sukses', "Draf penerimaan POY {$nomor} dihapus.");
    }

    /**
     * Aksi POSTING: DRAFT -> POSTED (tambah stok + alokasi buffer, satu transaksi).
     */
    public function posting(TransaksiMasuk $poy): RedirectResponse
    {
        Gate::authorize('posting', [$poy, self::JENIS]);

        try {
            $this->penerimaan->posting($poy, request()->user()?->id);
        } catch (AturanBisnisException $e) {
            return back()->withErrors(['posting' => $e->getMessage()]);
        }

        return redirect()
            ->route('inbound.poy.show', $poy)
            ->with('sukses', "Penerimaan POY {$poy->nomor_transaksi} berhasil DIPOSTING — stok aktif diperbarui.");
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    /**
     * Header transaksi dari input tervalidasi.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function headerDari(array $data): array
    {
        return [
            'tanggal' => $data['tanggal'],
            'partner_id' => $data['partner_id'] ?? null,
            'ref_dokumen' => $data['ref_dokumen'] ?? null,
            'shift' => $data['shift'] ?? null,
            'operator_nama' => $data['operator_nama'] ?? null,
            'pengirim' => $data['pengirim'] ?? null,
            'penerima' => $data['penerima'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ];
    }

    /**
     * Baris detail kosong untuk form awal.
     *
     * @return array<string, mixed>
     */
    private function barisKosong(): array
    {
        return [
            'barang_id' => null,
            'merge' => '',
            'grade_id' => null,
            'jumlah_box' => '',
            'jumlah_bobbin' => '',
            'berat_kg' => '',
        ];
    }

    /**
     * Status yang dapat dipilih pada filter index.
     *
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
