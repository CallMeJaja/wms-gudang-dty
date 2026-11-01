<?php

namespace App\Http\Controllers\Inbound;

use App\Enums\JenisMasuk;
use App\Enums\PosStok;
use App\Enums\StatusDokumen;
use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Inbound\Concerns\MenulisDetailPenerimaan;
use App\Http\Requests\Inbound\StorePenerimaanDtyRequest;
use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiMasukDetail;
use App\Services\KonversiSatuanService;
use App\Services\NomorDokumenService;
use App\Services\PenerimaanService;
use App\Services\StokService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Penerimaan DTY hasil produksi (PRD §4.4) — F2B.
 *
 * Alur: buat DRAFT -> "Posting" -> POSTED (stok DTY bertambah).
 * `merge_poy_asal` wajib untuk laporan rendemen (§6.3).
 * Gross per box di luar toleransi grade hanya menjadi PERINGATAN (§3.3).
 */
class PenerimaanDtyController extends Controller
{
    use MenulisDetailPenerimaan;

    private const JENIS = JenisMasuk::DtyProduksi;

    public function __construct(
        private readonly PenerimaanService $penerimaan,
        private readonly NomorDokumenService $nomor,
        private readonly KonversiSatuanService $konversi,
        private readonly StokService $stok,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', [TransaksiMasuk::class, self::JENIS]);

        $filter = $this->filterIndex($request, self::JENIS->value);

        $daftar = TransaksiMasuk::query()
            ->with(['pembuat'])
            ->withSum('detail as total_kg', 'berat_kg')
            ->where('jenis_masuk', self::JENIS)
            ->when($filter['dari'], fn ($q, $dari) => $q->whereDate('tanggal', '>=', $dari))
            ->when($filter['sampai'], fn ($q, $sampai) => $q->whereDate('tanggal', '<=', $sampai))
            ->when($filter['status'], fn ($q, $status) => $q->where('status', $status))
            ->when($filter['cari'] !== '', fn ($q) => $q->where(function ($q) use ($filter) {
                $q->where('nomor_transaksi', 'like', '%'.$filter['cari'].'%')
                    ->orWhere('merge_poy_asal', 'like', '%'.$filter['cari'].'%')
                    ->orWhere('operator_nama', 'like', '%'.$filter['cari'].'%');
            }))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('inbound.dty.index', [
            'daftar' => $daftar,
            'filter' => $filter,
            'daftarStatus' => $this->daftarStatus(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', [TransaksiMasuk::class, self::JENIS]);

        return view('inbound.dty.create', array_merge($this->dataMasterForm('DTY'), [
            'nomorPratinjau' => $this->nomor->peek('TRX_MASUK'),
            'sumberInternalDefault' => $this->sumberInternal(),
            'detailAwal' => old('detail', [$this->barisKosong()]),
            'payloadJs' => $this->payloadJs('DTY'),
            'toleransiGrade' => PenerimaanService::petaToleransiGrade(),
        ]));
    }

    public function store(StorePenerimaanDtyRequest $request): RedirectResponse
    {
        Gate::authorize('create', [TransaksiMasuk::class, self::JENIS]);

        try {
            $transaksi = DB::transaction(function () use ($request): TransaksiMasuk {
                $data = $request->validated();

                return tap(TransaksiMasuk::create(array_merge(
                    $this->headerDari($data),
                    [
                        'nomor_transaksi' => $this->nomorTransaksiBaru($this->nomor),
                        'jenis_masuk' => self::JENIS,
                        'sumber_internal' => $data['sumber_internal'] ?? $this->sumberInternal(),
                        'merge_poy_asal' => $data['merge_poy_asal'],
                        'status' => StatusDokumen::Draft,
                        'created_by' => $request->user()->id,
                    ],
                )), function (TransaksiMasuk $transaksi) use ($data): void {
                    $this->tulisDetail($transaksi, $data['detail'], $this->konversi);
                });
            });
        } catch (AturanBisnisException $e) {
            return back()->withInput()->withErrors(['detail' => $e->getMessage()]);
        }

        ActivityLog::catat(
            'CREATE',
            PenerimaanService::modul(self::JENIS),
            (int) $transaksi->id,
            "Buat draf penerimaan DTY {$transaksi->nomor_transaksi} (POY asal {$transaksi->merge_poy_asal}).",
        );

        return redirect()
            ->route('inbound.dty.show', $transaksi)
            ->with('sukses', "Draf penerimaan DTY {$transaksi->nomor_transaksi} tersimpan. Stok belum berubah — tekan \"Posting\" untuk memproses.");
    }

    public function show(TransaksiMasuk $dty): View
    {
        Gate::authorize('view', [$dty, self::JENIS]);

        $dty->load(['detail.barang.kategori', 'detail.grade', 'detail.satuanKemasan', 'pembuat']);

        return view('inbound.dty.show', [
            'transaksi' => $dty,
            'totalKg' => PenerimaanService::totalKg($dty),
            'peringatanGross' => $this->peringatanGross($dty),
        ]);
    }

    public function edit(TransaksiMasuk $dty): View
    {
        Gate::authorize('update', [$dty, self::JENIS]);

        $detail = old('detail', $this->detailKeArray($dty)->all());

        return view('inbound.dty.edit', array_merge($this->dataMasterForm('DTY'), [
            'transaksi' => $dty,
            'sumberInternalDefault' => $dty->sumber_internal ?: $this->sumberInternal(),
            'detailAwal' => $detail === [] ? [$this->barisKosong()] : $detail,
            'payloadJs' => $this->payloadJs('DTY'),
            'toleransiGrade' => PenerimaanService::petaToleransiGrade(),
        ]));
    }

    public function update(StorePenerimaanDtyRequest $request, TransaksiMasuk $dty): RedirectResponse
    {
        Gate::authorize('update', [$dty, self::JENIS]);

        try {
            DB::transaction(function () use ($request, $dty): void {
                $data = $request->validated();

                $dty->update(array_merge(
                    $this->headerDari($data),
                    [
                        'sumber_internal' => $data['sumber_internal'] ?? $this->sumberInternal(),
                        'merge_poy_asal' => $data['merge_poy_asal'],
                    ],
                ));

                $this->tulisDetail($dty, $data['detail'], $this->konversi);
            });
        } catch (AturanBisnisException $e) {
            return back()->withInput()->withErrors(['detail' => $e->getMessage()]);
        }

        ActivityLog::catat(
            'UPDATE',
            PenerimaanService::modul(self::JENIS),
            (int) $dty->id,
            "Ubah draf penerimaan DTY {$dty->nomor_transaksi}.",
        );

        return redirect()
            ->route('inbound.dty.show', $dty)
            ->with('sukses', "Draf penerimaan DTY {$dty->nomor_transaksi} diperbarui.");
    }

    public function destroy(TransaksiMasuk $dty): RedirectResponse
    {
        Gate::authorize('delete', [$dty, self::JENIS]);

        $nomor = $dty->nomor_transaksi;
        $id = (int) $dty->id;

        DB::transaction(function () use ($dty): void {
            $dty->detail()->delete();
            $dty->delete();
        });

        ActivityLog::catat(
            'DELETE',
            PenerimaanService::modul(self::JENIS),
            $id,
            "Hapus draf penerimaan DTY {$nomor}.",
        );

        return redirect()
            ->route('inbound.dty.index')
            ->with('sukses', "Draf penerimaan DTY {$nomor} dihapus.");
    }

    /**
     * Aksi POSTING: DRAFT -> POSTED (transaksi internal, stok DTY bertambah).
     */
    public function posting(TransaksiMasuk $dty): RedirectResponse
    {
        Gate::authorize('posting', [$dty, self::JENIS]);

        try {
            $this->penerimaan->posting($dty, request()->user()?->id);
        } catch (AturanBisnisException $e) {
            return back()->withErrors(['posting' => $e->getMessage()]);
        }

        $pesan = "Penerimaan DTY {$dty->nomor_transaksi} berhasil DIPOSTING — stok DTY diperbarui.";

        // Peringatan gross dicatat request saat simpan; ulangi peringatan
        // setelah posting agar tidak hilang dari halaman detail.
        $peringatan = $this->peringatanGross($dty);

        return redirect()
            ->route('inbound.dty.show', $dty)
            ->with('sukses', $pesan)
            ->with('peringatan', $peringatan);
    }

    /**
     * Pratinjau auto-fill DTY dari jumlah box (dipakai JS form).
     *
     * Memakai KonversiSatuanService::autoFillDty() sehingga angka selalu dari
     * config/wms.php. Respons: {bobbin, netto_kg, gross_kg}.
     */
    public function pratinjau(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', [TransaksiMasuk::class, self::JENIS]);

        $data = $request->validate([
            'jumlah_box' => ['required', 'numeric', 'min:0'],
        ]);

        return response()->json($this->penerimaan->autoFillDty((float) $data['jumlah_box']));
    }

    /**
     * Ringkasan stok satu kombinasi untuk panel samping detail (bantuan visual).
     */
    public function stokRingkas(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', [TransaksiMasuk::class, self::JENIS]);

        $data = $request->validate([
            'barang_id' => ['required', 'integer', 'exists:barang,id'],
            'merge' => ['required', 'string', 'max:50'],
            'grade_id' => ['required', 'integer', 'exists:grade,id'],
        ]);

        $barang = Barang::query()->findOrFail((int) $data['barang_id']);
        $saldo = $this->stok->saldo($barang, (string) $data['merge'], (int) $data['grade_id']);

        return response()->json([
            'stok_aktif' => $saldo !== null ? (float) $saldo->stok_aktif : 0.0,
            'stok_buffer' => $saldo !== null ? (float) $saldo->stok_buffer : 0.0,
            'pos' => PosStok::Aktif->value,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    /**
     * Peringatan gross per box di luar toleransi grade — dihitung ulang dari
     * data tersimpan; dipakai halaman detail (§3.3).
     *
     * @return array<int, string>
     */
    private function peringatanGross(TransaksiMasuk $transaksi): array
    {
        $transaksi->loadMissing(['detail.grade', 'detail.satuanKemasan']);

        $peringatan = [];

        foreach ($transaksi->detail as $i => $detail) {
            $box = $this->jumlahBoxDetail($detail);
            $gross = $this->grossDariDetail($detail);

            if ($box === null || $box <= 0 || $gross === null || $gross <= 0) {
                continue;
            }

            $grossPerBox = PenerimaanService::grossPerBox($gross, (float) $box);

            if ($grossPerBox === null || ! PenerimaanService::grossDiLuarToleransi($grossPerBox, $detail->grade?->kode)) {
                continue;
            }

            $rentang = PenerimaanService::rentangGrossGrade($detail->grade?->kode);

            $peringatan[] = sprintf(
                'Baris %d: gross per box %s kg di luar toleransi grade %s (%s–%s kg).',
                $i + 1,
                number_format($grossPerBox, 2, ',', '.'),
                (string) $detail->grade?->kode,
                number_format($rentang['min'], 2, ',', '.'),
                number_format($rentang['max'], 2, ',', '.'),
            );
        }

        return $peringatan;
    }

    /**
     * Gross kg dari detail.
     *
     * Skema `transaksi_masuk_detail` (migrasi F0, tidak boleh diubah) tidak
     * punya kolom berat_gross_kg — kolom itu hanya ada di detail keluar.
     * Nilai gross karena itu disimpan di `keterangan` dengan penanda
     * terstruktur `GROSS=<kg>` (lihat TransaksiMasukDetail::grossDariKeterangan).
     */
    private function grossDariDetail(TransaksiMasukDetail $detail): ?float
    {
        return TransaksiMasukDetail::grossDariKeterangan($detail->keterangan);
    }

    private function sumberInternal(): string
    {
        return (string) config('wms.inbound.sumber_internal_dty', 'AREA PRODUKSI');
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
            'jumlah_box' => '',
            'jumlah_bobbin' => '',
            'berat_kg' => '',
            'berat_gross_kg' => '',
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
