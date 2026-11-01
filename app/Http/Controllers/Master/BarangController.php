<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\BarangRequest;
use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\Satuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * CRUD master data Barang (F1). Hanya Admin yang boleh menulis (PRD §2).
 */
class BarangController extends Controller
{
    private const MODUL = 'MASTER_BARANG';

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Barang::class);

        $cari = trim((string) $request->query('cari', ''));

        $daftar = Barang::query()
            ->with(['kategori', 'satuanDasar'])
            ->when($cari !== '', function ($query) use ($cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('kode_barang', 'like', "%{$cari}%")
                        ->orWhere('nama_barang', 'like', "%{$cari}%");
                });
            })
            ->orderBy('kode_barang')
            ->paginate(15)
            ->withQueryString();

        return view('master.barang.index', [
            'daftar' => $daftar,
            'cari' => $cari,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Barang::class);

        return view('master.barang.create', $this->dataForm());
    }

    public function store(BarangRequest $request): RedirectResponse
    {
        Gate::authorize('create', Barang::class);

        $barang = Barang::create($request->validated());

        ActivityLog::catat(
            'CREATE',
            self::MODUL,
            $barang->id,
            "Tambah barang: {$barang->kode_barang} — {$barang->nama_barang}"
        );

        return redirect()
            ->route('master.barang.index')
            ->with('sukses', "Barang {$barang->kode_barang} berhasil ditambahkan.");
    }

    public function edit(Barang $barang): View
    {
        Gate::authorize('update', $barang);

        return view('master.barang.edit', $this->dataForm($barang));
    }

    public function update(BarangRequest $request, Barang $barang): RedirectResponse
    {
        Gate::authorize('update', $barang);

        $barang->update($request->validated());

        ActivityLog::catat(
            'UPDATE',
            self::MODUL,
            $barang->id,
            "Ubah barang: {$barang->kode_barang} — {$barang->nama_barang}"
        );

        return redirect()
            ->route('master.barang.index')
            ->with('sukses', "Barang {$barang->kode_barang} berhasil diperbarui.");
    }

    public function destroy(Barang $barang): RedirectResponse
    {
        Gate::authorize('delete', $barang);

        $kode = $barang->kode_barang;
        $barang->delete(); // SoftDeletes

        ActivityLog::catat('DELETE', self::MODUL, $barang->id, "Hapus barang (soft delete): {$kode}");

        return redirect()
            ->route('master.barang.index')
            ->with('sukses', "Barang {$kode} berhasil dihapus.");
    }

    /**
     * Data pendukung form create/edit.
     */
    private function dataForm(?Barang $barang = null): array
    {
        return [
            'barang' => $barang,
            'daftarKategori' => KategoriBarang::orderBy('kode')->get(),
            'daftarSatuan' => Satuan::orderBy('kode')->get(),
            'kategoriPoy' => KategoriBarang::POY,
        ];
    }
}
