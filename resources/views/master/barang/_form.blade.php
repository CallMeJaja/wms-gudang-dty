{{-- Form bersama barang. Variabel: $barang (nullable), $daftarKategori, $daftarSatuan, $kategoriPoy --}}
<div class="row g-3">
    <div class="col-md-4">
        <label for="kategori_id" class="form-label">Kategori <span class="text-danger">*</span></label>
        <select id="kategori_id" name="kategori_id"
                class="form-select @error('kategori_id') is-invalid @enderror" required>
            <option value="">— Pilih kategori —</option>
            @foreach ($daftarKategori as $kategori)
                <option value="{{ $kategori->id }}"
                    @selected((string) old('kategori_id', $barang?->kategori_id) === (string) $kategori->id)
                    data-poy="{{ $kategori->kode === $kategoriPoy ? '1' : '0' }}">
                    {{ $kategori->kode }} — {{ $kategori->nama }}
                </option>
            @endforeach
        </select>
        @error('kategori_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="satuan_dasar_id" class="form-label">Satuan Dasar <span class="text-danger">*</span></label>
        <select id="satuan_dasar_id" name="satuan_dasar_id"
                class="form-select @error('satuan_dasar_id') is-invalid @enderror" required>
            <option value="">— Pilih satuan —</option>
            @foreach ($daftarSatuan as $satuan)
                <option value="{{ $satuan->id }}"
                    @selected((string) old('satuan_dasar_id', $barang?->satuan_dasar_id) === (string) $satuan->id)>
                    {{ $satuan->kode }} — {{ $satuan->nama }}
                </option>
            @endforeach
        </select>
        @error('satuan_dasar_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="stok_minimum" class="form-label">Stok Minimum</label>
        <input type="number" step="0.01" min="0" id="stok_minimum" name="stok_minimum"
               class="form-control wms-num @error('stok_minimum') is-invalid @enderror"
               value="{{ old('stok_minimum', $barang?->stok_minimum) }}" placeholder="0">
        @error('stok_minimum')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="kode_barang" class="form-label">Kode Barang <span class="text-danger">*</span></label>
        <input type="text" id="kode_barang" name="kode_barang"
               class="form-control @error('kode_barang') is-invalid @enderror"
               value="{{ old('kode_barang', $barang?->kode_barang) }}" required maxlength="50">
        @error('kode_barang')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8">
        <label for="nama_barang" class="form-label">Nama Barang <span class="text-danger">*</span></label>
        <input type="text" id="nama_barang" name="nama_barang"
               class="form-control @error('nama_barang') is-invalid @enderror"
               value="{{ old('nama_barang', $barang?->nama_barang) }}" required maxlength="150">
        @error('nama_barang')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="denier" class="form-label">Denier</label>
        <input type="text" id="denier" name="denier"
               class="form-control @error('denier') is-invalid @enderror"
               value="{{ old('denier', $barang?->denier) }}" placeholder="mis. 250" maxlength="20">
        @error('denier')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="filament" class="form-label">Filament</label>
        <input type="text" id="filament" name="filament"
               class="form-control @error('filament') is-invalid @enderror"
               value="{{ old('filament', $barang?->filament) }}" placeholder="mis. 48" maxlength="20">
        @error('filament')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <div class="form-check">
            <input type="checkbox" id="is_merge_minggu" name="is_merge_minggu" value="1"
                   class="form-check-input @error('is_merge_minggu') is-invalid @enderror"
                   @checked(old('is_merge_minggu', $barang?->is_merge_minggu))>
            <label class="form-check-label" for="is_merge_minggu">
                Merge Minggu (hanya untuk barang kategori POY)
            </label>
            @error('is_merge_minggu')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-check mt-1">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   class="form-check-input @error('is_active') is-invalid @enderror"
                   @checked(old('is_active', $barang?->is_active ?? true))>
            <label class="form-check-label" for="is_active">Aktif</label>
            @error('is_active')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
