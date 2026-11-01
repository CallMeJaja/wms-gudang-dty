{{-- Form bersama partner. Variabel: $partner (nullable), $daftarJenis --}}
<div class="row g-3">
    <div class="col-md-4">
        <label for="kode" class="form-label">Kode <span class="text-danger">*</span></label>
        <input type="text" id="kode" name="kode"
               class="form-control @error('kode') is-invalid @enderror"
               value="{{ old('kode', $partner?->kode) }}" required maxlength="30">
        @error('kode')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-5">
        <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
        <input type="text" id="nama" name="nama"
               class="form-control @error('nama') is-invalid @enderror"
               value="{{ old('nama', $partner?->nama) }}" required maxlength="150">
        @error('nama')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="jenis" class="form-label">Jenis <span class="text-danger">*</span></label>
        <select id="jenis" name="jenis" class="form-select @error('jenis') is-invalid @enderror" required>
            <option value="">— Pilih jenis —</option>
            @foreach ($daftarJenis as $j)
                <option value="{{ $j }}" @selected(old('jenis', $partner?->jenis) === $j)>{{ $j }}</option>
            @endforeach
        </select>
        @error('jenis')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="kontak" class="form-label">Kontak</label>
        <input type="text" id="kontak" name="kontak"
               class="form-control @error('kontak') is-invalid @enderror"
               value="{{ old('kontak', $partner?->kontak) }}" maxlength="100">
        @error('kontak')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="alamat" class="form-label">Alamat</label>
        <textarea id="alamat" name="alamat" rows="2"
                  class="form-control @error('alamat') is-invalid @enderror" maxlength="255">{{ old('alamat', $partner?->alamat) }}</textarea>
        @error('alamat')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
