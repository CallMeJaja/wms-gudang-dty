# Troubleshooting

Kumpulan masalah umum dan langkah diagnosis.

## Perubahan kode tidak muncul

Gejala: file PHP sudah diubah tetapi aplikasi masih menampilkan perilaku
lama.

Penyebab umum:

- OPcache menyimpan bytecode hingga **180 detik**.
- Cache konfigurasi, route, atau view masih aktif.
- PHP-FPM belum di-reload.

Langkah:

```bash
php artisan optimize:clear
php artisan optimize
```

Lalu reload PHP-FPM. Untuk pengembangan, pastikan
`opcache.validate_timestamps=1` atau matikan OPcache di CLI/web lokal.
Ingat bahwa konfigurasi OPcache CLI dan web bisa berbeda.

## “Unable to locate file in Vite manifest”

Gejala: halaman gagal render karena aset frontend tidak ditemukan.

Langkah:

```bash
npm install
npm run build
```

Untuk pengembangan:

```bash
npm run dev
```

Pastikan folder `public/build` ada dan tidak terhapus. Bila memakai
`composer run dev`, pastikan proses Vite ikut berjalan.

## Login gagal

- Login panel memakai **email**, bukan username.
- URL login: `/admin/login`.
- Root `/` dan `/login` redirect ke panel.
- Pastikan user aktif (`is_active = true`).
- Akun default hanya ada setelah seeder atau impor dump.
- Bila password lupa, ubah melalui menu Pengguna sebagai Admin atau buat
  ulang akun di lingkungan lokal.

## 403 setelah login

Gejala: user berhasil login tetapi tidak bisa membuka menu tertentu.

Penyebab:

- Manajer bersifat read-only.
- Operator tidak boleh mengubah master data, void, opname, pengguna, atau
  riwayat aktivitas.
- Hanya Admin yang dapat membuka Pengguna dan Riwayat Aktivitas.
- Halaman cetak hanya tersedia untuk dokumen yang sudah diposting atau
  diterbitkan.
- User nonaktif tidak dapat mengakses panel.

Periksa role pada menu Pengguna dan status aktif user.

## Impor SQL gagal

- Dump manual ini **tidak** membuat database dan **tidak** memakai `DROP TABLE`.
- Buat database terlebih dahulu, lalu impor ke database yang **wajib kosong**.
- Jika database sudah memiliki tabel, kosongkan atau buat ulang terlebih
  dahulu.
- Pastikan user MySQL memiliki izin membuat tabel.
- Gunakan `utf8mb4` dan `utf8mb4_general_ci`.
- Bila muncul peringatan collation, buat database dengan collation yang
  diminta sebelum impor.
- Setelah impor, cocokkan `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`
  di `.env`.
- Jalankan `php artisan config:clear` setelah mengubah `.env`.

Perintah import:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS db_wms_gudang_dty CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
mysql -u root -p db_wms_gudang_dty < database_backup/db_wms_gudang_dty.sql
```

## Nomor dokumen duplikat atau tidak sesuai preview

- Nomor baru memakai `generateUnik()` sehingga melewati nomor yang sudah
  dipakai record lama/manual.
- Preview memakai `peekUnik()` dan tidak menaikkan counter.
- Nomor Gate Pass dan Surat Jalan read-only di form; jangan mengubahnya
  langsung dari database.
- Bila counter terlihat tertinggal, biarkan generator melewati nomor yang
  sudah ada; jangan menurunkan `last_number` secara manual tanpa backup.

## Nomor Gate Pass / Surat Jalan tidak bisa diedit

- Keduanya memang read-only dan dibuat otomatis di server.
- Untuk koreksi dokumen fisik, gunakan alur pembatalan/void yang berlaku,
  bukan mengedit nomor langsung.

## Waste / Scrap tidak mengurangi stok

- Ini perilaku yang benar. Waste/Scrap adalah log kg tanpa grade, merge,
  item, atau packing.
- Waste log tidak membuat mutasi stok DTY.

## Opname ditolak

- Opname hanya boleh pada hari terakhir bulan.
- Untuk item pendukung, merge otomatis `-` dan grade `STD`; tidak perlu
  memilih merge/grade.
- Backdated opname ditolak bila sudah ada mutasi setelah tanggal tersebut.

## Tabel notifikasi tidak ditemukan

Gejala: drawer notifikasi error atau badge tidak muncul.

Langkah:

```bash
php artisan migrate --force
php artisan optimize:clear
```

Pastikan tabel `notifications` ada. Notifikasi dibuat saat runtime setelah
mutasi stok atau saat menjalankan `php artisan wms:notifikasi-stok-menipis`.

## Shortcut pencarian tidak berfungsi

- Shortcut global search adalah **Ctrl+K** di Windows/Linux dan **⌘K** di
  macOS.
- Beberapa ekstensi browser memakai shortcut yang sama; coba klik kolom
  pencarian di sidebar.
- Pastikan sidebar tidak dalam mode tertutup.
- Setelah deploy, bersihkan cache aset dan muat ulang halaman.

## Export PDF gagal

- Pastikan ekstensi `mbstring`, `gd`, dan `fileinfo` aktif.
- Pastikan `storage/` dapat ditulis.
- Filter terlalu besar dapat melewati batas memori PHP; naikkan
  `memory_limit` atau persempit periode.
- Pastikan font dan view tersedia di `resources/views/laporan/`.
- Cek log Laravel di `storage/logs/`.

## Export Excel gagal

- Pastikan `maatwebsite/excel` terpasang.
- Pastikan filter periode valid.
- Cek izin tulis `storage/` dan log aplikasi.
- Untuk data besar, persempit filter atau naikkan batas memori.

## Buffer tidak terisi

Periksa:

1. Barang berstatus POY dan `is_merge_minggu = true`.
2. Tanggal transaksi adalah Senin–Sabtu.
3. Transaksi sudah `POSTED`, bukan `DRAFT`.
4. Pasokan hari itu minimal 7 pallet.
5. Total buffer gudang belum mencapai 1 pallet.
6. Kombinasi barang, merge, dan tanggal belum pernah dialokasikan.

Bila pasokan kurang dari 7 pallet, buffer memang sengaja dilewati dan
dicatat dengan `dilewati = true`.

## Saldo stok terlihat tidak konsisten

- `stok_barang` adalah saldo berjalan.
- `mutasi_stok.saldo_akhir_qty` adalah snapshot audit, bukan sumber
  kebenaran.
- Jalankan pratinjau rekalkulasi:

  ```bash
  php artisan wms:recalculate-stock --dry-run
  ```

- Bila hasil pratinjau wajar, jalankan tanpa `--dry-run` setelah backup.
- Jangan mengubah saldo langsung di database tanpa memahami ledger.

## Scheduler tidak berjalan

- Pastikan cron `schedule:run` aktif setiap menit.
- Periksa `php artisan schedule:list`.
- Pastikan zona waktu aplikasi benar.
- Cek log aplikasi dan log cron.
- Untuk lokal, gunakan `php artisan schedule:work`.

## Test gagal karena konfigurasi

```bash
php artisan config:clear
php artisan test
```

Test memakai SQLite in-memory. Jangan mengarahkan `DB_CONNECTION` test ke
MySQL lokal.

## Aset atau halaman 404 setelah deploy

- Pastikan document root mengarah ke `public/`.
- Jalankan `npm run build`.
- Jalankan `php artisan storage:link`.
- Periksa aturan rewrite web server untuk Laravel.
- Bersihkan cache route dan view.
