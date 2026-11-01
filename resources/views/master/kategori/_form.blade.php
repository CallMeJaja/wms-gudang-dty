{{-- Form bersama kategori. Variabel: $kategori (nullable) --}}
<div class="row g-3">
    <div class="col-md-4">
        <label for="kode" class="form-label">Kode <span class="text-danger">*</span></label>
        <input type="text" id="kode" name="kode"
               class="form-control @error('kode') is-invalid @enderror"
               value="{{ old('kode', $kategori?->kode) }}" required maxlength="20">
        @error('kode')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8">
        <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
        <input type="text" id="nama" name="nama"
               class="form-control @error('nama') is-invalid @enderror"
               value="{{ old('nama', $kategori?->nama) }}" required maxlength="100">
        @error('nama')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="keterangan" class="form-label">Keterangan</label>
        <input type="text" id="keterangan" name="keterangan"
               class="form-control @error('keterangan') is-invalid @enderror"
               value="{{ old('keterangan', $kategori?->keterangan) }}" maxlength="255">
        @error('keterangan')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
