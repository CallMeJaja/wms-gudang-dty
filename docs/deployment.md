# Deployment

Checklist ini untuk men-deploy aplikasi ke server produksi. Jangan
menjalankan perintah yang mengubah database produksi tanpa backup.

## 1. Environment

Siapkan `.env` produksi:

```dotenv
APP_NAME="WMS Gudang DTY PEI"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://wms.example.com
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_wms_gudang_dty
DB_USERNAME=<user-produksi>
DB_PASSWORD=<password-kuat>
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_general_ci

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

LOG_CHANNEL=stack
LOG_LEVEL=warning
```

Catatan:

- Gunakan user database khusus aplikasi, bukan `root`.
- `APP_DEBUG` wajib `false`.
- Pastikan `APP_URL` memakai HTTPS.
- Zona waktu default `Asia/Jakarta` agar jadwal rilis buffer tepat.

## 2. Dependency

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Jangan menjalankan `composer update` atau mengubah dependency di server
produksi tanpa perencanaan.

## 3. Application key

Untuk instalasi baru:

```bash
php artisan key:generate --force
```

Jangan membuat ulang key pada aplikasi yang sudah berjalan karena sesi dan
data terenkripsi dapat rusak.

## 4. Migrasi

```bash
php artisan migrate --force
```

Aturan:

- Jangan memakai `migrate:fresh` atau `migrate:refresh` di produksi.
- Jangan mengimpor dump demo ke produksi.
- Backup database sebelum migrasi.
- Pastikan tabel `notifications` ikut termigrasi.
- Untuk instalasi baru tanpa data demo, gunakan migrasi dan seeder master
  sesuai kebutuhan operasional.

## 5. Storage dan permission

```bash
php artisan storage:link
```

Pastikan direktori berikut dapat ditulis oleh user web server:

```text
storage/
bootstrap/cache/
```

Contoh kepemilikan:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
```

## 6. Optimasi

```bash
php artisan optimize
```

Perintah ini menyatukan cache konfigurasi, route, dan view. Setelah
mengubah `.env` atau konfigurasi, jalankan:

```bash
php artisan optimize:clear
php artisan optimize
```

## 7. Scheduler

Tambahkan cron agar scheduler Laravel berjalan setiap menit:

```cron
* * * * * cd /path/ke/wms-gudang-dty && php artisan schedule:run >> /dev/null 2>&1
```

Jadwal aplikasi:

- `wms:rilis-buffer-minggu` setiap Minggu pukul 05:45 waktu aplikasi.
- Command rilis bersifat idempoten; buffer nol tidak menghasilkan error.

Verifikasi:

```bash
php artisan schedule:list
```

## 8. Queue worker

Konfigurasi default memakai queue database. Jalankan worker dengan
Supervisor atau process manager lain:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=120
```

Pastikan worker otomatis restart dan log-nya dipantau. Setelah deploy,
jalankan:

```bash
php artisan queue:restart
```

Bila aplikasi tidak memakai job asynchronous, worker tetap disarankan agar
job bawaan tidak menumpuk.

## 9. Web server dan HTTPS

- Arahkan document root ke folder `public/`.
- Aktifkan HTTPS dan redirect HTTP ke HTTPS.
- Teruskan header proxy yang diperlukan bila memakai reverse proxy.
- Batasi akses ke `.env`, `storage/`, dan file backup.
- Jangan mengekspos `database_backup/` melalui web server.

## 10. Backup

Backup rutin:

```bash
mysqldump -u <user> -p --single-transaction --routines --triggers db_wms_gudang_dty > backup-$(date +%F).sql
```

Simpan backup di lokasi terpisah dari server aplikasi. Uji proses restore
secara berkala. Ingat bahwa dump aplikasi memakai `DROP TABLE`, jadi
restore harus ke database yang benar.

## 11. Notifikasi

- Tabel `notifications` harus ada.
- Notifikasi database tampil di sidebar dan dikirim langsung (sinkron),
  sehingga tidak memerlukan queue worker.
- Pemeriksaan stok menipis berjalan otomatis setelah mutasi stok dan lewat
  command terjadwal `wms:notifikasi-stok-menipis` setiap jam.
- `NotifikasiDemoSeeder` menjalankan pemeriksaan stok yang sebenarnya; tidak
  membuat notifikasi statis.
- Setelah migrasi, verifikasi dengan memicu mutasi stok atau menjalankan
  `php artisan wms:notifikasi-stok-menipis`, lalu buka drawer notifikasi.

## 12. Keamanan akun

- Ganti password akun default sebelum aplikasi dipakai.
- Nonaktifkan akun yang tidak lagi digunakan.
- Gunakan role sesuai kebutuhan; Manajer read-only.
- Batasi akses panel melalui jaringan atau VPN bila memungkinkan.
- Audit akses melalui menu Riwayat Aktivitas.

## 13. Verifikasi setelah deploy

1. Buka `/` dan pastikan redirect ke `/admin`.
2. Login dengan akun non-default.
3. Periksa Beranda, grafik, dan notifikasi.
4. Buat transaksi uji di lingkungan staging, bukan produksi.
5. Unduh satu laporan Excel dan PDF.
6. Cetak Gate Pass dan Surat Jalan dari data uji.
7. Pastikan scheduler dan queue berjalan.
8. Periksa log aplikasi dan log web server.
9. Pastikan `APP_DEBUG=false` dan tidak ada stack trace yang tampil.

## 14. Rollback

- Simpan rilis sebelumnya dan backup database sebelum migrasi.
- Bila deploy gagal, kembalikan kode ke rilis sebelumnya.
- Restore database hanya bila migrasi tidak kompatibel dan backup sudah
  diverifikasi.
- Setelah rollback, jalankan `php artisan optimize:clear` lalu
  `php artisan optimize`.
