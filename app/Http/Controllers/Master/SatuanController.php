<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\SatuanRequest;
use App\Models\ActivityLog;
use App\Models\Satuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * CRUD sederhana master data Satuan (F1).
 * Data referensi kritis → tidak boleh dihapus bila sudah dipakai.
 */
class SatuanController extends Controller
{
    private const MODUL = 'MASTER_SATUAN';

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Satuan::class);

        $cari = trim((string) $request->query('cari', ''));

        $daftar = Satuan::query()
            ->withCount('barang')
            ->when($cari !== '', function ($query) use ($cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('kode', 'like', "%{$cari}%")
                        ->orWhere('nama', 'like', "%{$cari}%");
                });
            })
            ->orderBy('kode')
            ->paginate(15)
            ->withQueryString();

        return view('master.satuan.index', [
            'daftar' => $daftar,
            'cari' => $cari,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Satuan::class);

        return view('master.satuan.create', ['satuan' => null]);
    }

    public function store(SatuanRequest $request): RedirectResponse
    {
        Gate::authorize('create', Satuan::class);

        $satuan = Satuan::create($request->validated());

        ActivityLog::catat('CREATE', self::MODUL, $satuan->id, "Tambah satuan: {$satuan->kode} — {$satuan->nama}");

        return redirect()
            ->route('master.satuan.index')
            ->with('sukses', "Satuan {$satuan->kode} berhasil ditambahkan.");
    }

    public function edit(Satuan $satuan): View
    {
        Gate::authorize('update', $satuan);

        return view('master.satuan.edit', ['satuan' => $satuan]);
    }

    public function update(SatuanRequest $request, Satuan $satuan): RedirectResponse
    {
        Gate::authorize('update', $satuan);

        $satuan->update($request->validated());

        ActivityLog::catat('UPDATE', self::MODUL, $satuan->id, "Ubah satuan: {$satuan->kode} — {$satuan->nama}");

        return redirect()
            ->route('master.satuan.index')
            ->with('sukses', "Satuan {$satuan->kode} berhasil diperbarui.");
    }

    public function destroy(Satuan $satuan): RedirectResponse
    {
        Gate::authorize('delete', $satuan);

        if ($satuan->barang()->exists()) {
            throw ValidationException::withMessages([
                'satuan' => "Satuan {$satuan->kode} sudah dipakai pada master barang sehingga tidak dapat dihapus.",
            ]);
        }

        $kode = $satuan->kode;
        $id = $satuan->id;
        $satuan->delete();

        ActivityLog::catat('DELETE', self::MODUL, $id, "Hapus satuan: {$kode}");

        return redirect()
            ->route('master.satuan.index')
            ->with('sukses', "Satuan {$kode} berhasil dihapus.");
    }
}
