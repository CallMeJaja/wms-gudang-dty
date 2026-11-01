<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\PartnerRequest;
use App\Models\ActivityLog;
use App\Models\SupplierCustomer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * CRUD master data Supplier/Customer (F1).
 */
class PartnerController extends Controller
{
    private const MODUL = 'MASTER_PARTNER';

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', SupplierCustomer::class);

        $cari = trim((string) $request->query('cari', ''));
        $jenis = trim((string) $request->query('jenis', ''));

        $daftar = SupplierCustomer::query()
            ->when($cari !== '', function ($query) use ($cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('kode', 'like', "%{$cari}%")
                        ->orWhere('nama', 'like', "%{$cari}%");
                });
            })
            ->when($jenis !== '' && in_array($jenis, SupplierCustomer::JENIS, true), function ($query) use ($jenis) {
                $query->where('jenis', $jenis);
            })
            ->orderBy('kode')
            ->paginate(15)
            ->withQueryString();

        return view('master.partner.index', [
            'daftar' => $daftar,
            'cari' => $cari,
            'jenis' => $jenis,
            'daftarJenis' => SupplierCustomer::JENIS,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', SupplierCustomer::class);

        return view('master.partner.create', $this->dataForm());
    }

    public function store(PartnerRequest $request): RedirectResponse
    {
        Gate::authorize('create', SupplierCustomer::class);

        $partner = SupplierCustomer::create($request->validated());

        ActivityLog::catat(
            'CREATE',
            self::MODUL,
            $partner->id,
            "Tambah partner: {$partner->kode} — {$partner->nama} ({$partner->jenis})"
        );

        return redirect()
            ->route('master.partner.index')
            ->with('sukses', "Partner {$partner->kode} berhasil ditambahkan.");
    }

    public function edit(SupplierCustomer $partner): View
    {
        Gate::authorize('update', $partner);

        return view('master.partner.edit', $this->dataForm($partner));
    }

    public function update(PartnerRequest $request, SupplierCustomer $partner): RedirectResponse
    {
        Gate::authorize('update', $partner);

        $partner->update($request->validated());

        ActivityLog::catat(
            'UPDATE',
            self::MODUL,
            $partner->id,
            "Ubah partner: {$partner->kode} — {$partner->nama} ({$partner->jenis})"
        );

        return redirect()
            ->route('master.partner.index')
            ->with('sukses', "Partner {$partner->kode} berhasil diperbarui.");
    }

    public function destroy(SupplierCustomer $partner): RedirectResponse
    {
        Gate::authorize('delete', $partner);

        $kode = $partner->kode;
        $partner->delete(); // SoftDeletes

        ActivityLog::catat('DELETE', self::MODUL, $partner->id, "Hapus partner (soft delete): {$kode}");

        return redirect()
            ->route('master.partner.index')
            ->with('sukses', "Partner {$kode} berhasil dihapus.");
    }

    private function dataForm(?SupplierCustomer $partner = null): array
    {
        return [
            'partner' => $partner,
            'daftarJenis' => SupplierCustomer::JENIS,
        ];
    }
}
