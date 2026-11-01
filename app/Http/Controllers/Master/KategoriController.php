<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\KategoriRequest;
use App\Models\ActivityLog;
use App\Models\KategoriBarang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * CRUD sederhana master data Kategori Barang (F1).
 * Data referensi kritis → tidak boleh dihapus bila sudah dipakai.
 */
class KategoriController extends Controller
{
    private const MODUL = 'MASTER_KATEGORI';

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', KategoriBarang::class);

        $cari = trim((string) $request->query('cari', ''));

        $daftar = KategoriBarang::query()
            ->withCount(['barang', 'grade'])
            ->when($cari !== '', function ($query) use ($cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('kode', 'like', "%{$cari}%")
                        ->orWhere('nama', 'like', "%{$cari}%");
                });
            })
            ->orderBy('kode')
            ->paginate(15)
            ->withQueryString();

        return view('master.kategori.index', [
            'daftar' => $daftar,
            'cari' => $cari,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', KategoriBarang::class);

        return view('master.kategori.create', ['kategori' => null]);
    }

    public function store(KategoriRequest $request): RedirectResponse
    {
        Gate::authorize('create', KategoriBarang::class);

        $kategori = KategoriBarang::create($request->validated());

        ActivityLog::catat('CREATE', self::MODUL, $kategori->id, "Tambah kategori: {$kategori->kode} — {$kategori->nama}");

        return redirect()
            ->route('master.kategori.index')
            ->with('sukses', "Kategori {$kategori->kode} berhasil ditambahkan.");
    }

    public function edit(KategoriBarang $kategori): View
    {
        Gate::authorize('update', $kategori);

        return view('master.kategori.edit', ['kategori' => $kategori]);
    }

    public function update(KategoriRequest $request, KategoriBarang $kategori): RedirectResponse
    {
        Gate::authorize('update', $kategori);

        $kategori->update($request->validated());

        ActivityLog::catat('UPDATE', self::MODUL, $kategori->id, "Ubah kategori: {$kategori->kode} — {$kategori->nama}");

        return redirect()
            ->route('master.kategori.index')
            ->with('sukses', "Kategori {$kategori->kode} berhasil diperbarui.");
    }

    public function destroy(KategoriBarang $kategori): RedirectResponse
    {
        Gate::authorize('delete', $kategori);

        if ($kategori->barang()->exists() || $kategori->grade()->exists()) {
            throw ValidationException::withMessages([
                'kategori' => "Kategori {$kategori->kode} sudah dipakai pada master barang atau grade "
                    .'sehingga tidak dapat dihapus.',
            ]);
        }

        $kode = $kategori->kode;
        $id = $kategori->id;
        $kategori->delete();

        ActivityLog::catat('DELETE', self::MODUL, $id, "Hapus kategori: {$kode}");

        return redirect()
            ->route('master.kategori.index')
            ->with('sukses', "Kategori {$kode} berhasil dihapus.");
    }
}
