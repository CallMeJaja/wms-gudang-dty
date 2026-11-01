<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\GradeRequest;
use App\Models\ActivityLog;
use App\Models\Grade;
use App\Models\KategoriBarang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * CRUD master data Grade (F1).
 *
 * Grade C pada kategori DTY dilindungi: tidak boleh dihapus/nonaktif
 * (konfirmasi PIC — scrap grade C pernah keluar, PRD §3.5).
 */
class GradeController extends Controller
{
    private const MODUL = 'MASTER_GRADE';

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Grade::class);

        $cari = trim((string) $request->query('cari', ''));

        $daftar = Grade::query()
            ->with('kategori')
            ->when($cari !== '', function ($query) use ($cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('kode', 'like', "%{$cari}%")
                        ->orWhere('keterangan', 'like', "%{$cari}%");
                });
            })
            ->orderBy('kategori_id')
            ->orderBy('kode')
            ->paginate(15)
            ->withQueryString();

        return view('master.grade.index', [
            'daftar' => $daftar,
            'cari' => $cari,
            'kodeScrap' => Grade::KODE_SCRAP,
            'kategoriDty' => KategoriBarang::DTY,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Grade::class);

        return view('master.grade.create', $this->dataForm());
    }

    public function store(GradeRequest $request): RedirectResponse
    {
        Gate::authorize('create', Grade::class);

        $grade = Grade::create($request->validated());

        ActivityLog::catat(
            'CREATE',
            self::MODUL,
            $grade->id,
            "Tambah grade: {$grade->kode} ({$grade->kategori?->kode})"
        );

        return redirect()
            ->route('master.grade.index')
            ->with('sukses', "Grade {$grade->kode} berhasil ditambahkan.");
    }

    public function edit(Grade $grade): View
    {
        Gate::authorize('update', $grade);

        return view('master.grade.edit', $this->dataForm($grade));
    }

    public function update(GradeRequest $request, Grade $grade): RedirectResponse
    {
        Gate::authorize('update', $grade);

        $grade->update($request->validated());

        ActivityLog::catat(
            'UPDATE',
            self::MODUL,
            $grade->id,
            "Ubah grade: {$grade->kode} ({$grade->kategori?->kode}), aktif: "
            .($grade->is_active ? 'ya' : 'tidak')
        );

        return redirect()
            ->route('master.grade.index')
            ->with('sukses', "Grade {$grade->kode} berhasil diperbarui.");
    }

    public function destroy(Grade $grade): RedirectResponse
    {
        Gate::authorize('delete', $grade);

        // Proteksi grade scrap C DTY (konfirmasi PIC — scrap grade C pernah keluar).
        if ($grade->kode === Grade::KODE_SCRAP && $grade->kategori?->kode === KategoriBarang::DTY) {
            throw ValidationException::withMessages([
                'grade' => 'Grade C pada kategori DTY adalah grade scrap yang wajib dipertahankan '
                    .'(konfirmasi PIC: scrap grade C pernah keluar). Grade ini tidak dapat dihapus. '
                    .'Nonaktifkan saja bila benar-benar tidak dipakai.',
            ]);
        }

        if ($grade->stokBarang()->exists()) {
            throw ValidationException::withMessages([
                'grade' => "Grade {$grade->kode} sudah dipakai pada stok barang sehingga tidak dapat dihapus. "
                    .'Nonaktifkan saja bila tidak dipakai lagi.',
            ]);
        }

        $kode = $grade->kode;
        $id = $grade->id;
        $grade->delete();

        ActivityLog::catat('DELETE', self::MODUL, $id, "Hapus grade: {$kode}");

        return redirect()
            ->route('master.grade.index')
            ->with('sukses', "Grade {$kode} berhasil dihapus.");
    }

    private function dataForm(?Grade $grade = null): array
    {
        return [
            'grade' => $grade,
            'daftarKategori' => KategoriBarang::orderBy('kode')->get(),
        ];
    }
}
