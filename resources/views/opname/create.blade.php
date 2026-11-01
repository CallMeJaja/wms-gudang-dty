@extends('layouts.app')

@section('title', 'Opname Baru — Stock Opname')

@section('content')
    <div class="mb-3">
        <div class="small text-body-secondary mb-1">
            <a href="{{ route('opname.index') }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Stock Opname
            </a>
        </div>
        <p class="text-secondary small mb-0">
            Selisih dihitung otomatis (fisik &minus; sistem); penyesuaian dibuat otomatis dan langsung POSTED.
        </p>
    </div>

    <form method="POST" action="{{ route('opname.store') }}" class="col-lg-6">
        @csrf

        <div class="mb-3">
            <label for="barang_id" class="form-label">Barang <span class="text-danger">*</span></label>
            <select name="barang_id" id="barang_id" class="form-select" required data-wms-barang>
                <option value="">— Pilih barang —</option>
                @foreach ($daftarBarang as $barang)
                    <option value="{{ $barang->id }}" data-kategori="{{ $barang->kategori_id }}" @selected(old('barang_id') == $barang->id)>
                        {{ $barang->kode_barang }} — {{ $barang->nama_barang }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="merge" class="form-label">Merge <span class="text-danger">*</span></label>
            <input type="text" name="merge" id="merge" value="{{ old('merge') }}" class="form-control" maxlength="50" required>
        </div>

        <div class="mb-3">
            <label for="grade_id" class="form-label">Grade <span class="text-danger">*</span></label>
            <select name="grade_id" id="grade_id" class="form-select" required data-wms-grade>
                <option value="">— Pilih grade —</option>
                @foreach ($daftarGrade as $grade)
                    <option value="{{ $grade->id }}" data-kategori="{{ $grade->kategori_id }}" @selected(old('grade_id') == $grade->id)>
                        {{ $grade->kode }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="stok_fisik_qty" class="form-label">Stok Fisik (kg) <span class="text-danger">*</span></label>
            <input type="number" name="stok_fisik_qty" id="stok_fisik_qty" value="{{ old('stok_fisik_qty') }}"
                   class="form-control wms-num" step="0.01" min="0" required>
        </div>

        <div class="mb-3">
            <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
            <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan <span class="text-body-secondary">(opsional)</span></label>
            <input type="text" name="keterangan" id="keterangan" value="{{ old('keterangan') }}" class="form-control" maxlength="255">
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Opname</button>
            <a href="{{ route('opname.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.querySelector('[data-wms-barang]')?.addEventListener('change', (e) => {
    const kategori = e.target.selectedOptions[0]?.dataset.kategori;
    document.querySelectorAll('[data-wms-grade] option').forEach((opt) => {
        opt.hidden = opt.value !== '' && kategori !== undefined && opt.dataset.kategori !== kategori;
    });
});
</script>
@endpush
