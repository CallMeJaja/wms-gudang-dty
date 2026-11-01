{{-- Form bersama konversi. Variabel: $konversi (nullable), $daftarBarang, $daftarSatuan --}}
<div class="row g-3">
    <div class="col-md-6">
        <label for="barang_id" class="form-label">Barang</label>
        <select id="barang_id" name="barang_id" class="form-select @error('barang_id') is-invalid @enderror">
            <option value="">— Global (berlaku untuk semua barang non-POY) —</option>
            @foreach ($daftarBarang as $barang)
                <option value="{{ $barang->id }}"
                    @selected((string) old('barang_id', $konversi?->barang_id) === (string) $barang->id)>
                    {{ $barang->kode_barang }} — {{ $barang->nama_barang }}
                </option>
            @endforeach
        </select>
        @error('barang_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Barang POY sengaja tidak ditampilkan (POY tidak boleh dikonversi, A1b).</div>
    </div>

    <div class="col-md-3">
        <label for="dari_satuan_id" class="form-label">Satuan Asal <span class="text-danger">*</span></label>
        <select id="dari_satuan_id" name="dari_satuan_id"
                class="form-select @error('dari_satuan_id') is-invalid @enderror" required>
            <option value="">— Pilih —</option>
            @foreach ($daftarSatuan as $satuan)
                <option value="{{ $satuan->id }}"
                    @selected((string) old('dari_satuan_id', $konversi?->dari_satuan_id) === (string) $satuan->id)>
                    {{ $satuan->kode }}
                </option>
            @endforeach
        </select>
        @error('dari_satuan_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="ke_satuan_id" class="form-label">Satuan Tujuan <span class="text-danger">*</span></label>
        <select id="ke_satuan_id" name="ke_satuan_id"
                class="form-select @error('ke_satuan_id') is-invalid @enderror" required>
            <option value="">— Pilih —</option>
            @foreach ($daftarSatuan as $satuan)
                <option value="{{ $satuan->id }}"
                    @selected((string) old('ke_satuan_id', $konversi?->ke_satuan_id) === (string) $satuan->id)>
                    {{ $satuan->kode }}
                </option>
            @endforeach
        </select>
        @error('ke_satuan_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="faktor" class="form-label">Faktor <span class="text-danger">*</span></label>
        <input type="number" step="0.0001" min="0.0001" id="faktor" name="faktor"
               class="form-control wms-num @error('faktor') is-invalid @enderror"
               value="{{ old('faktor', $konversi?->faktor) }}" required>
        @error('faktor')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Contoh: 1 BOBBIN = {{ rtrim(rtrim(number_format((float) config('wms.satuan.dty_bobbin_kg'), 2, ',', '.'), '0'), ',') }} KG (DTY) → satuan asal BOBBIN, tujuan KG.</div>
    </div>

    <div class="col-12">
        <div class="form-check">
            <input type="hidden" name="is_default" value="0">
            <input type="checkbox" id="is_default" name="is_default" value="1"
                   class="form-check-input @error('is_default') is-invalid @enderror"
                   @checked(old('is_default', $konversi?->is_default))>
            <label class="form-check-label" for="is_default">Jadikan konversi default</label>
            @error('is_default')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
