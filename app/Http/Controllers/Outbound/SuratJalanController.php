<?php

namespace App\Http\Controllers\Outbound;

use App\Enums\JenisKeluar;
use App\Enums\StatusDokumen;
use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Outbound\Concerns\MenulisDetailPengeluaran;
use App\Http\Requests\Outbound\StoreSuratJalanRequest;
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
 * Pengiriman DTY via Surat Jalan (PRD §4.5) — F3B.
 *
 * Dokumen resmi: DRAFT -> ISSUED. Nomor Surat Jalan digenerate atomik saat
 * issue (atau dipakai manual bila diisi). Stok berkurang HANYA saat ISSUED.
 */
class SuratJalanController extends Controller
{
    use MenulisDetailPengeluaran;

    private const JENIS = JenisKeluar::DtySuratJalan;

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
            ->with(['partner', 'pembuat'])
            ->withSum('detail as total_netto', 'berat_netto_kg')
            ->withSum('detail as total_gross', 'berat_gross_kg')
            ->withCount('detail')
            ->where('jenis_keluar', self::JENIS)
            ->when($filter['dari'], fn ($q, $dari) => $q->whereDate('tanggal', '>=', $dari))
            ->when($filter['sampai'], fn ($q, $sampai) => $q->whereDate('tanggal', '<=', $sampai))
            ->when($filter['status'], fn ($q, $status) => $q->where('status', $status))
            ->when($filter['cari'] !== '', fn ($q) => $q->where(function ($q) use ($filter) {
                $q->where('nomor_transaksi', 'like', '%'.$filter['cari'].'%')
                    ->orWhere('nomor_surat_jalan', 'like', '%'.$filter['cari'].'%')
                    ->orWhere('nama_sopir', 'like', '%'.$filter['cari'].'%');
            }))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('outbound.surat-jalan.index', [
            'daftar' => $daftar,
            'filter' => $filter,
            'daftarStatus' => $this->daftarStatus(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', [TransaksiKeluar::class, self::JENIS]);

        return view('outbound.surat-jalan.create', array_merge($this->dataMasterForm('DTY', true), [
            'nomorPratinjau' => $this->nomor->peek('TRX_KELUAR'),
            'nomorSjPratinjau' => $this->nomor->peek('SURAT_JALAN_DTY'),
            'detailAwal' => old('detail', [$this->barisKosong()]),
            'payloadJs' => $this->payloadJs('DTY', true),
        ]));
    }

    public function store(StoreSuratJalanRequest $request): RedirectResponse
    {
        Gate::authorize('create', [TransaksiKeluar::class, self::JENIS]);

        try {
            $transaksi = DB::transaction(function () use ($request): TransaksiKeluar {
                $data = $request->validated();

                $transaksi = TransaksiKeluar::create(array_merge(
                    $this->headerDari($data),
                    [
                        'nomor_transaksi' => $this->nomorTransaksiBaru($this->nomor),
                        'jenis_keluar' => self::JENIS,
                        'nomor_surat_jalan' => $this->nomorSuratJalan($data),
                        'status' => StatusDokumen::Draft,
                        'created_by' => $request->user()->id,
                    ],
                ));

                $this->tulisDetail($transaksi, $data['detail']);

                return $transaksi;
            });
        } catch (AturanBisnisException $e) {
            return back()->withInput()->withErrors(['detail' => $e->getMessage()]);
        }

        ActivityLog::catat(
            'CREATE',
            PengeluaranService::modul(self::JENIS),
            (int) $transaksi->id,
            "Buat draf Surat Jalan {$transaksi->nomor_transaksi} ({$transaksi->detail()->count()} baris).",
        );

        return redirect()
            ->route('outbound.surat-jalan.show', $transaksi)
            ->with('sukses', "Draf Surat Jalan {$transaksi->nomor_transaksi} tersimpan. Stok belum berubah — tekan \"Terbitkan\" untuk memproses.");
    }

    public function show(TransaksiKeluar $suratJalan): View
    {
        Gate::authorize('view', [$suratJalan, self::JENIS]);

        $suratJalan->load(['detail.barang.kategori', 'detail.grade', 'detail.satuanKemasan', 'partner', 'pembuat', 'pemvoid']);

        return view('outbound.surat-jalan.show', [
            'transaksi' => $suratJalan,
            'totalNetto' => PengeluaranService::totalNetto($suratJalan),
            'totalGross' => PengeluaranService::totalGross($suratJalan),
        ]);
    }

    /**
     * Halaman cetak Surat Jalan (format fisik §4.5 + bukti SJ 244).
     */
    public function cetak(TransaksiKeluar $suratJalan): View
    {
        Gate::authorize('view', [$suratJalan, self::JENIS]);

        $suratJalan->load(['detail.barang.kategori', 'detail.grade', 'detail.satuanKemasan', 'partner']);

        return view('outbound.surat-jalan.cetak', [
            'transaksi' => $suratJalan,
            'totalNetto' => PengeluaranService::totalNetto($suratJalan),
            'totalGross' => PengeluaranService::totalGross($suratJalan),
        ]);
    }

    public function edit(TransaksiKeluar $suratJalan): View
    {
        Gate::authorize('update', [$suratJalan, self::JENIS]);

        $detail = old('detail', $this->detailKeArray($suratJalan)->all());

        return view('outbound.surat-jalan.edit', array_merge($this->dataMasterForm('DTY', true), [
            'transaksi' => $suratJalan,
            'nomorSjPratinjau' => $this->nomor->peek('SURAT_JALAN_DTY'),
            'detailAwal' => $detail === [] ? [$this->barisKosong()] : $detail,
            'payloadJs' => $this->payloadJs('DTY', true),
        ]));
    }

    public function update(StoreSuratJalanRequest $request, TransaksiKeluar $suratJalan): RedirectResponse
    {
        Gate::authorize('update', [$suratJalan, self::JENIS]);

        try {
            DB::transaction(function () use ($request, $suratJalan): void {
                $data = $request->validated();

                $suratJalan->update(array_merge(
                    $this->headerDari($data),
                    ['nomor_surat_jalan' => $this->nomorSuratJalan($data, $suratJalan)],
                ));

                $this->tulisDetail($suratJalan, $data['detail']);
            });
        } catch (AturanBisnisException $e) {
            return back()->withInput()->withErrors(['detail' => $e->getMessage()]);
        }

        ActivityLog::catat(
            'UPDATE',
            PengeluaranService::modul(self::JENIS),
            (int) $suratJalan->id,
            "Ubah draf Surat Jalan {$suratJalan->nomor_transaksi}.",
        );

        return redirect()
            ->route('outbound.surat-jalan.show', $suratJalan)
            ->with('sukses', "Draf Surat Jalan {$suratJalan->nomor_transaksi} diperbarui.");
    }

    public function destroy(TransaksiKeluar $suratJalan): RedirectResponse
    {
        Gate::authorize('delete', [$suratJalan, self::JENIS]);

        $nomor = $suratJalan->nomor_transaksi;
        $id = (int) $suratJalan->id;

        DB::transaction(function () use ($suratJalan): void {
            $suratJalan->detail()->delete();
            $suratJalan->delete();
        });

        ActivityLog::catat(
            'DELETE',
            PengeluaranService::modul(self::JENIS),
            $id,
            "Hapus draf Surat Jalan {$nomor}.",
        );

        return redirect()
            ->route('outbound.surat-jalan.index')
            ->with('sukses', "Draf Surat Jalan {$nomor} dihapus.");
    }

    public function issue(TransaksiKeluar $suratJalan): RedirectResponse
    {
        Gate::authorize('issue', [$suratJalan, self::JENIS]);

        try {
            $this->pengeluaran->issue($suratJalan, request()->user()?->id);
        } catch (AturanBisnisException $e) {
            return back()->withErrors(['issue' => $e->getMessage()]);
        }

        return redirect()
            ->route('outbound.surat-jalan.show', $suratJalan->fresh())
            ->with('sukses', "Surat Jalan {$suratJalan->fresh()->nomor_surat_jalan} berhasil DITERBITKAN — stok berkurang.");
    }

    public function void(Request $request, TransaksiKeluar $suratJalan): RedirectResponse
    {
        Gate::authorize('void', [$suratJalan, self::JENIS]);

        $data = $request->validate([
            'alasan_void' => ['required', 'string', 'min:'.VoidTransaksiService::MIN_ALASAN, 'max:1000'],
        ]);

        try {
            $this->voidService->voidTransaksiKeluar($suratJalan, $data['alasan_void'], $request->user()->id);
        } catch (AturanBisnisException $e) {
            return back()->withErrors(['alasan_void' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('outbound.surat-jalan.show', $suratJalan)
            ->with('sukses', "Surat Jalan {$suratJalan->nomor_transaksi} DIBATALKAN (void) — stok dikembalikan.");
    }

    /**
     * Nomor SJ: manual bila diisi, kosong (null) bila tidak — digenerate saat issue.
     *
     * @param  array<string, mixed>  $data
     */
    private function nomorSuratJalan(array $data, ?TransaksiKeluar $transaksi = null): ?string
    {
        $manual = trim((string) ($data['nomor_surat_jalan'] ?? ''));

        if ($manual !== '') {
            return $manual;
        }

        return $transaksi?->nomor_surat_jalan;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function headerDari(array $data): array
    {
        return [
            'tanggal' => $data['tanggal'],
            'partner_id' => $data['partner_id'] ?? null,
            'no_kendaraan' => $data['no_kendaraan'] ?? null,
            'nama_sopir' => $data['nama_sopir'] ?? null,
            'pic_pengirim' => $data['pic_pengirim'] ?? null,
            'pic_mengetahui' => $data['pic_mengetahui'] ?? null,
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
            StatusDokumen::Issued->value => StatusDokumen::Issued->label(),
            StatusDokumen::Cancelled->value => StatusDokumen::Cancelled->label(),
            StatusDokumen::Void->value => StatusDokumen::Void->label(),
        ];
    }
}
