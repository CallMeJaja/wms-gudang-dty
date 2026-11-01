# Contributing

Panduan ini menjaga konsistensi kode, riwayat Git, dan dokumentasi.

## Branch

- Buat branch dari branch pengembangan yang disepakati.
- Gunakan nama deskriptif, misalnya `feat/buffer-minggu`,
  `fix/validasi-stok`, atau `docs/panduan-deploy`.
- Jangan mencampur refactor besar dengan perubahan perilaku dalam satu
  branch.

## Conventional Commits

Format pesan commit:

```text
<tipe>(<scope>): <ringkasan>
```

Tipe yang dipakai:

| Tipe | Kegunaan |
| --- | --- |
| `feat` | Fitur baru |
| `fix` | Perbaikan bug |
| `docs` | Dokumentasi |
| `refactor` | Perubahan struktur tanpa mengubah perilaku |
| `test` | Menambah atau memperbaiki test |
| `chore` | Tugas rutin, dependency, konfigurasi |
| `style` | Formatting tanpa perubahan logika |
| `perf` | Peningkatan performa |
| `build` | Build system atau aset |
| `ci` | Konfigurasi integrasi |

Contoh:

```text
feat(penerimaan): tambahkan validasi merge wajib
fix(stok): tolak pengurangan melebihi saldo aktif
docs(database): jelaskan constraint buffer
```

Gunakan bahasa Indonesia yang ringkas dan imperatif.

## Commit atomic

- Satu commit = satu perubahan logis.
- Pisahkan dokumentasi, refactor, dan perbaikan bug bila memungkinkan.
- Jangan menggabungkan file yang tidak terkait.
- Jangan commit file rahasia, dump lokal, atau data sensitif.
- Periksa `git status` dan `git diff` sebelum commit.

## Sebelum commit

1. Jalankan test yang terdampak:

   ```bash
   php artisan test --compact
   ```

2. Rapikan format PHP:

   ```bash
   vendor/bin/pint --dirty
   ```

3. Build aset bila tampilan berubah:

   ```bash
   npm run build
   ```

4. Perbarui dokumentasi bila perilaku, URL, atau aturan berubah.

## Gaya kode

- Gunakan tipe parameter dan return type eksplisit.
- Gunakan kurung kurawal untuk semua struktur kontrol.
- Gunakan constructor property promotion bila sesuai.
- Gunakan enum untuk nilai tetap.
- Gunakan PHPDoc untuk array shape dan logika kompleks.
- Hindari komentar yang hanya mengulang kode.
- Tulis komentar untuk menjelaskan alasan, bukan mengulang implementasi.
- Jangan menulis angka bisnis di kode; gunakan `config/wms.php`.
- Jangan menulis referensi ke dokumen internal, kode dokumen, atau nama
  orang pada komentar.
- Jangan menyalin data sensitif ke test, dokumentasi, atau commit.

## Service dan policy

- Perubahan saldo stok harus melalui `StokService`.
- Orkestrasi transaksi harus melalui service terkait.
- Gunakan `DB::transaction()` dan `lockForUpdate()` untuk operasi yang
  mengubah saldo atau status.
- Validasi role dan status di policy serta service.
- Jangan menulis saldo langsung dari komponen Filament.
- Jangan menduplikasi aturan bisnis yang sudah ada di service.
- Bila menambah jenis transaksi, perbarui enum, policy, form, service,
  laporan, dan test terkait.

## Database

- Buat migrasi dengan `php artisan make:migration`.
- Jangan mengubah migrasi yang sudah pernah dijalankan di environment
  bersama; buat migrasi baru.
- Sertakan foreign key, unique constraint, dan index yang relevan.
- Untuk perubahan destruktif, siapkan rencana backup dan rollback.
- Perbarui seeder bila master data berubah.
- Jangan menjalankan `migrate:fresh` pada database berisi data penting.

## Test

- Tambahkan test untuk perubahan perilaku dan failure mode penting.
- Gunakan feature test untuk HTTP, panel, dan service.
- Gunakan unit test untuk logika murni.
- Gunakan factory dan helper data uji yang sudah ada.
- Jangan mengarahkan test ke database lokal.
- Pastikan test tidak bergantung pada urutan eksekusi.

## Dokumentasi

- Dokumentasi berada di `docs/`.
- Ikuti struktur Diátaxis: tutorial, how-to, reference, explanation.
- Gunakan tautan relatif.
- Perbarui `docs/features.md` bila menu atau URL berubah.
- Perbarui `docs/business-rules.md` bila aturan bisnis berubah.
- Perbarui `docs/database.md` bila skema berubah.
- Perbarui `docs/testing.md` bila jumlah atau struktur test berubah.
- Jangan menambahkan dokumen baru tanpa kebutuhan yang jelas.

## Review

Sebelum mengajukan perubahan, periksa:

- Apakah perubahan sesuai ruang lingkup?
- Apakah ada test yang membuktikan perilaku baru?
- Apakah error dan edge case ditangani?
- Apakah akses role sudah benar?
- Apakah data sensitif aman?
- Apakah dokumentasi masih akurat?
- Apakah `git diff` bersih dari file yang tidak disengaja?
