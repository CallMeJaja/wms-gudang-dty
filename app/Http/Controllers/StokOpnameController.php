<?php

namespace App\Http\Controllers;

use App\Exceptions\AturanBisnisException;
use App\Models\Barang;
use App\Models\Grade;
use App\Models\StokOpname;
use App\Services\StokOpnameService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StokOpnameController extends Controller
{
    public function __construct(private readonly StokOpnameService $opname) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', StokOpname::class);

        $daftar = StokOpname::query()
            ->with(['barang.kategori', 'grade', 'pembuat'])
            ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->string('dari')))
            ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->string('sampai')))
            ->when($request->filled('barang_id'), fn ($q) => $q->where('barang_id', $request->integer('barang_id')))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('opname.index', [
            'daftar' => $daftar,
            'filter' => $request->only(['dari', 'sampai', 'barang_id']),
            'daftarBarang' => Barang::query()->aktif()->orderBy('kode_barang')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', StokOpname::class);

        $daftarBarang = Barang::query()->aktif()->with('kategori')->orderBy('kode_barang')->get();
        $daftarGrade = Grade::query()->aktif()->orderBy('kode')->get();

        return view('opname.create', [
            'daftarBarang' => $daftarBarang,
            'daftarGrade' => $daftarGrade,
            'payloadJs' => [
                'barang' => $daftarBarang->map(fn (Barang $b) => [
                    'id' => $b->id,
                    'kategoriId' => $b->kategori_id,
                ])->values()->all(),
                'grade' => $daftarGrade->map(fn (Grade $g) => [
                    'id' => $g->id,
                    'kode' => $g->kode,
                    'kategoriId' => $g->kategori_id,
                ])->values()->all(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', StokOpname::class);

        $data = $request->validate([
            'barang_id' => ['required', 'integer', 'exists:barang,id'],
            'merge' => ['required', 'string', 'max:50'],
            'grade_id' => ['required', 'integer', 'exists:grade,id'],
            'stok_fisik_qty' => ['required', 'numeric', 'min:0'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $opname = $this->opname->proses(
                Barang::findOrFail($data['barang_id']),
                $data['merge'],
                (int) $data['grade_id'],
                (float) $data['stok_fisik_qty'],
                (string) $data['tanggal'],
                $request->user()->id,
                $data['keterangan'] ?? null,
            );
        } catch (AturanBisnisException $e) {
            return back()->withInput()->withErrors(['opname' => $e->getMessage()]);
        }

        return redirect()
            ->route('opname.show', $opname)
            ->with('sukses', "Opname {$opname->nomor_opname} tersimpan — stok disinkronkan dengan fisik.");
    }

    public function show(StokOpname $opname): View
    {
        Gate::authorize('view', $opname);

        $opname->load(['barang.kategori', 'grade', 'pembuat', 'transaksiMasuk', 'transaksiKeluar']);

        return view('opname.show', ['opname' => $opname]);
    }
}
