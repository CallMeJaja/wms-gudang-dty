<?php

namespace App\Http\Controllers\Inbound;

use App\Enums\JenisMasuk;
use App\Enums\StatusDokumen;
use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Inbound\Concerns\MenulisDetailPenerimaan;
use App\Http\Requests\Inbound\StorePenerimaanPendukungRequest;
use App\Models\ActivityLog;
use App\Models\TransaksiMasuk;
use App\Services\KonversiSatuanService;
use App\Services\NomorDokumenService;
use App\Services\PenerimaanService;
use App\Services\VoidTransaksiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Penerimaan item pendukung via Gate Pass (PRD §4.2) — F2B.
 *
 * Dokumen resmi: lifecycle DRAFT -> ISSUED -> CANCELLED.
 * Stok bertambah saat status menjadi ISSUED (aksi "Terbitkan / Issue").
 */
class PenerimaanPendukungController extends Controller
{
    use MenulisDetailPenerimaan;

    private const JENIS = JenisMasuk::PendukungGatepass;

    public function __construct(
        private readonly PenerimaanService $penerimaan,
        private readonly NomorDokumenService $nomor,
        private readonly KonversiSatuanService $konversi,
        private readonly VoidTransaksiService $voidService,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', [TransaksiMasuk::class, self::JENIS]);

        $filter = $this->filterIndex($request, self::JENIS->value);

        $daftar = TransaksiMasuk::query()
            ->with(['partner', 'pembuat'])
            ->withSum('detail as total_kg', 'berat_kg')
            ->withCount('detail')
            ->where('jenis_masuk', self::JENIS)
            ->when($filter['dari'], fn ($q, $dari) => $q->whereDate('tanggal', '>=', $dari))
            ->when($filter['sampai'], fn ($q, $sampai) => $q->whereDate('tanggal', '<=', $sampai))
            ->when($filter['status'], fn ($q, $status) => $q->where('status', $status))
            ->when($filter['cari'] !== '', fn ($q) => $q->where(function ($q) use ($filter) {
                $q->where('nomor_transaksi', 'like', '%'.$filter['cari'].'%')
                    ->orWhere('nomor_gate_pass', 'like', '%'.$filter['cari'].'%')
                    ->orWhere('ref_dokumen', 'like', '%'.$filter['cari'].'%');
            }))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('inbound.pendukung.index', [
            'daftar' => $daftar,
            'filter' => $filter,
            'daftarStatus' => $this->daftarStatus(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', [TransaksiMasuk::class, self::JENIS]);

        return view('inbound.pendukung.create', array_merge($this->dataMasterForm('PENDUKUNG', true), [
            // Nomor Gate Pass di-generate dari service tetapi BOLEH diedit manual
            // karena nomor fisik berasal dari dokumen Gate Pass IPCI (PRD §4.2).
            'nomorGatePass' => old('nomor_gate_pass', $this->nomor->peek('GATE_PASS')),
            'detailAwal' => old('detail', [$this->barisKosong()]),
            'payloadJs' => $this->payloadJs('PENDUKUNG', true),
        ]));
    }

    public function store(StorePenerimaanPendukungRequest $request): RedirectResponse
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
                        'nomor_gate_pass' => $this->nomorGatePass($data),
                        'status' => StatusDokumen::Draft,
                        'created_by' => $request->user()->id,
                    ],
                )), function (TransaksiMasuk $transaksi) use ($data): void {
                    $this->tulisDetail($transaksi, $this->lengkapiGradePendukung($data['detail']), $this->konversi);
                });
            });
        } catch (AturanBisnisException $e) {
            return back()->withInput()->withErrors(['detail' => $e->getMessage()]);
        }

        ActivityLog::catat(
            'CREATE',
            PenerimaanService::modul(self::JENIS),
            (int) $transaksi->id,
            "Buat draf Gate Pass {$transaksi->nomor_gate_pass} ({$transaksi->nomor_transaksi}).",
        );

        return redirect()
            ->route('inbound.pendukung.show', $transaksi)
            ->with('sukses', "Draf Gate Pass {$transaksi->nomor_gate_pass} tersimpan. Stok belum berubah — tekan \"Terbitkan\" untuk memproses.");
    }

    public function show(TransaksiMasuk $pendukung): View
    {
        Gate::authorize('view', [$pendukung, self::JENIS]);

        $pendukung->load(['detail.barang.kategori', 'detail.grade', 'detail.satuanKemasan', 'partner', 'pembuat', 'pemvoid']);

        return view('inbound.pendukung.show', [
            'transaksi' => $pendukung,
            'totalKg' => PenerimaanService::totalKg($pendukung),
        ]);
    }

    public function edit(TransaksiMasuk $pendukung): View
    {
        Gate::authorize('update', [$pendukung, self::JENIS]);

        $detail = old('detail', $this->detailKeArray($pendukung)->all());

        return view('inbound.pendukung.edit', array_merge($this->dataMasterForm('PENDUKUNG', true), [
            'transaksi' => $pendukung,
            'nomorGatePass' => old('nomor_gate_pass', $pendukung->nomor_gate_pass),
            'detailAwal' => $detail === [] ? [$this->barisKosong()] : $detail,
            'payloadJs' => $this->payloadJs('PENDUKUNG', true),
        ]));
    }

    public function update(StorePenerimaanPendukungRequest $request, TransaksiMasuk $pendukung): RedirectResponse
    {
        Gate::authorize('update', [$pendukung, self::JENIS]);

        try {
            DB::transaction(function () use ($request, $pendukung): void {
                $data = $request->validated();

                $pendukung->update(array_merge(
                    $this->headerDari($data),
                    ['nomor_gate_pass' => $this->nomorGatePass($data, $pendukung)],
                ));

                $this->tulisDetail($pendukung, $this->lengkapiGradePendukung($data['detail']), $this->konversi);
            });
        } catch (AturanBisnisException $e) {
            return back()->withInput()->withErrors(['detail' => $e->getMessage()]);
        }

        ActivityLog::catat(
            'UPDATE',
            PenerimaanService::modul(self::JENIS),
            (int) $pendukung->id,
            "Ubah draf Gate Pass {$pendukung->nomor_gate_pass}.",
        );

        return redirect()
            ->route('inbound.pendukung.show', $pendukung)
            ->with('sukses', "Draf Gate Pass {$pendukung->nomor_gate_pass} diperbarui.");
    }

    public function destroy(TransaksiMasuk $pendukung): RedirectResponse
    {
        Gate::authorize('delete', [$pendukung, self::JENIS]);

        $nomor = (string) $pendukung->nomor_gate_pass;
        $id = (int) $pendukung->id;

        DB::transaction(function () use ($pendukung): void {
            $pendukung->detail()->delete();
            $pendukung->delete();
        });

        ActivityLog::catat(
            'DELETE',
            PenerimaanService::modul(self::JENIS),
            $id,
            "Hapus draf Gate Pass {$nomor}.",
        );

        return redirect()
            ->route('inbound.pendukung.index')
            ->with('sukses', "Draf Gate Pass {$nomor} dihapus.");
    }

    /**
     * Aksi ISSUE: DRAFT -> ISSUED (stok bertambah, satu DB transaction).
     */
    public function issue(TransaksiMasuk $pendukung): RedirectResponse
    {
        Gate::authorize('issue', [$pendukung, self::JENIS]);

        try {
            $this->penerimaan->posting($pendukung, request()->user()?->id);
        } catch (AturanBisnisException $e) {
            return back()->withErrors(['issue' => $e->getMessage()]);
        }

        return redirect()
            ->route('inbound.pendukung.show', $pendukung)
            ->with('sukses', "Gate Pass {$pendukung->nomor_gate_pass} berhasil DITERBITKAN — stok diperbarui.");
    }

    /**
     * Aksi VOID: ISSUED -> VOID (balik mutasi stok, hanya Admin) — F6.
     * Nomor Gate Pass tetap tersimpan (tidak didaur ulang).
     */
    public function void(Request $request, TransaksiMasuk $pendukung): RedirectResponse
    {
        Gate::authorize('void', [$pendukung, self::JENIS]);

        $data = $request->validate([
            'alasan_void' => ['required', 'string', 'min:'.VoidTransaksiService::MIN_ALASAN, 'max:1000'],
        ]);

        try {
            $this->voidService->voidTransaksiMasuk($pendukung, $data['alasan_void'], $request->user()->id);
        } catch (AturanBisnisException $e) {
            return back()->withErrors(['alasan_void' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('inbound.pendukung.show', $pendukung)
            ->with('sukses', "Gate Pass {$pendukung->nomor_gate_pass} DIBATALKAN (void) — stok dikembalikan.");
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    /**
     * Item pendukung tidak meminta grade: isi grade netral kategori PENDUKUNG.
     *
     * @param  array<int, array<string, mixed>>  $detail
     * @return array<int, array<string, mixed>>
     */
    private function lengkapiGradePendukung(array $detail): array
    {
        $gradeId = $this->gradePendukung()->id;

        foreach ($detail as $i => $baris) {
            if (empty($baris['grade_id'])) {
                $detail[$i]['grade_id'] = $gradeId;
            }
        }

        return $detail;
    }

    /**
     * Nomor Gate Pass yang dipakai: input manual bila diisi, atau digenerate
     * atomik dari NomorDokumenService (bila kosong).
     *
     * @param  array<string, mixed>  $data
     */
    private function nomorGatePass(array $data, ?TransaksiMasuk $transaksi = null): string
    {
        $manual = trim((string) ($data['nomor_gate_pass'] ?? ''));
        $lama = $transaksi?->nomor_gate_pass;

        // Saat edit tanpa perubahan nomor, jangan menaikkan counter lagi.
        if ($manual !== '' && $manual === $lama) {
            return $manual;
        }

        if ($manual !== '') {
            return $manual;
        }

        return $this->nomor->generate('GATE_PASS');
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
            StatusDokumen::Issued->value => StatusDokumen::Issued->label(),
            StatusDokumen::Cancelled->value => StatusDokumen::Cancelled->label(),
            StatusDokumen::Void->value => StatusDokumen::Void->label(),
        ];
    }
}
