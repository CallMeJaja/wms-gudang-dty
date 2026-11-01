{{-- Form bersama grade. Variabel: $grade (nullable), $daftarKategori --}}
<div class="row g-3">
    <div class="col-md-4">
        <label for="kategori_id" class="form-label">Kategori <span class="text-danger">*</span></label>
        <select id="kategori_id" name="kategori_id"
                class="form-select @error('kategori_id') is-invalid @enderror" required>
            <option value="">— Pilih kategori —</option>
            @foreach ($daftarKategori as $kategori)
                <option value="{{ $kategori->id }}"
                    @selected((string) old('kategori_id', $grade?->kategori_id) === (string) $kategori->id)>
                    {{ $kategori->kode }} — {{ $kategori->nama }}
                </option>
            @endforeach
        </select>
        @error('kategori_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="kode" class="form-label">Kode Grade <span class="text-danger">*</span></label>
        <input type="text" id="kode" name="kode"
               class="form-control @error('kode') is-invalid @enderror"
               value="{{ old('kode', $grade?->kode) }}" required maxlength="10">
        @error('kode')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="keterangan" class="form-label">Keterangan</label>
        <input type="text" id="keterangan" name="keterangan"
               class="form-control @error('keterangan') is-invalid @enderror"
               value="{{ old('keterangan', $grade?->keterangan) }}" maxlength="255">
        @error('keterangan')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   class="form-check-input @error('is_active') is-invalid @enderror"
                   @checked(old('is_active', $grade?->is_active ?? true))>
            <label class="form-check-label" for="is_active">Aktif</label>
            @error('is_active')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-text">Grade C pada kategori DTY tidak dapat dinonaktifkan (scrap wajib tercatat).</div>
    </div>
</div>
