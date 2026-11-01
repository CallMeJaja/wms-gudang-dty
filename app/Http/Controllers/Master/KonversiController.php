<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\KonversiSatuanRequest;
use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\KonversiSatuan;
use App\Models\Satuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * CRUD master data Konversi Satuan (F1).
 *
 * POY tidak boleh memiliki konversi (A1b) — ditegakkan di Form Request.
 */
class KonversiController extends Controller
{
    private const MODUL = 'MASTER_KONVERSI';

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', KonversiSatuan::class);

        $cari = trim((string) $request->query('cari', ''));

        $daftar = KonversiSatuan::query()
            ->with(['barang', 'dariSatuan', 'keSatuan'])
            ->when($cari !== '', function ($query) use ($cari) {
                $query->where(function ($q) use ($cari) {
                    $q->whereHas('barang', fn ($b) => $b->where('kode_barang', 'like', "%{$cari}%")
                        ->orWhere('nama_barang', 'like', "%{$cari}%"));
                });
            })
            ->orderBy('barang_id')
            ->orderBy('dari_satuan_id')
            ->orderBy('ke_satuan_id')
            ->paginate(15)
            ->withQueryString();

        return view('master.konversi.index', [
            'daftar' => $daftar,
            'cari' => $cari,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', KonversiSatuan::class);

        return view('master.konversi.create', $this->dataForm());
    }

    public function store(KonversiSatuanRequest $request): RedirectResponse
    {
        Gate::authorize('create', KonversiSatuan::class);

        $konversi = KonversiSatuan::create($request->validated());
        $konversi->load(['barang', 'dariSatuan', 'keSatuan']);

        ActivityLog::catat('CREATE', self::MODUL, $konversi->id, 'Tambah konversi: '.$this->ringkas($konversi));

        return redirect()
            ->route('master.konversi.index')
            ->with('sukses', 'Konversi satuan berhasil ditambahkan.');
    }

    public function edit(KonversiSatuan $konversi): View
    {
        Gate::authorize('update', $konversi);

        return view('master.konversi.edit', $this->dataForm($konversi));
    }

    public function update(KonversiSatuanRequest $request, KonversiSatuan $konversi): RedirectResponse
    {
        Gate::authorize('update', $konversi);

        $konversi->update($request->validated());
        $konversi->load(['barang', 'dariSatuan', 'keSatuan']);

        ActivityLog::catat('UPDATE', self::MODUL, $konversi->id, 'Ubah konversi: '.$this->ringkas($konversi));

        return redirect()
            ->route('master.konversi.index')
            ->with('sukses', 'Konversi satuan berhasil diperbarui.');
    }

    public function destroy(KonversiSatuan $konversi): RedirectResponse
    {
        Gate::authorize('delete', $konversi);

        $konversi->load(['barang', 'dariSatuan', 'keSatuan']);
        $ringkas = $this->ringkas($konversi);
        $id = $konversi->id;
        $konversi->delete();

        ActivityLog::catat('DELETE', self::MODUL, $id, 'Hapus konversi: '.$ringkas);

        return redirect()
            ->route('master.konversi.index')
            ->with('sukses', 'Konversi satuan berhasil dihapus.');
    }

    private function dataForm(?KonversiSatuan $konversi = null): array
    {
        return [
            'konversi' => $konversi,
            // Hanya barang non-POY yang boleh dikonversi (A1b); POY dikecualikan
            // dari pilihan agar tidak bisa dipilih.
            'daftarBarang' => Barang::query()
                ->with('kategori')
                ->whereHas('kategori', fn ($q) => $q->where('kode', '!=', KategoriBarang::POY))
                ->orderBy('kode_barang')
                ->get(),
            'daftarSatuan' => Satuan::orderBy('kode')->get(),
        ];
    }

    private function ringkas(KonversiSatuan $konversi): string
    {
        $barang = $konversi->barang?->kode_barang ?? 'Global';
        $dari = $konversi->dariSatuan?->kode ?? '?';
        $ke = $konversi->keSatuan?->kode ?? '?';

        return "{$barang}: 1 {$dari} = {$konversi->faktor} {$ke}";
    }
}
