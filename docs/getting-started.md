# Getting Started

Panduan ini membantu menjalankan WMS Gudang DTY dari repositori kosong
sampai bisa login ke panel admin.

## 1. Prasyarat

| Kebutuhan | Versi / catatan |
| --- | --- |
| PHP | 8.3 atau lebih baru |
| Ekstensi PHP | `openssl`, `pdo`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` |
| Composer | Versi terbaru yang stabil |
| Node.js | 20 atau lebih baru |
| npm | Mengikuti Node.js |
| Database | MySQL 8 atau MariaDB |
| Git | Untuk clone repositori |

Untuk ekspor PDF dan Excel, pastikan ekstensi `gd`, `mbstring`, dan
`fileinfo` aktif.

## 2. Clone dan dependency

```bash
git clone <URL_REPOSITORY> wms-gudang-dty
cd wms-gudang-dty
composer install
npm install
```

## 3. Konfigurasi environment

```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database di `.env`:

```dotenv
APP_NAME="WMS Gudang DTY PEI"
APP_ENV=local
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_wms_gudang_dty
DB_USERNAME=<user-database>
DB_PASSWORD=<password-database>
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_general_ci
```

## 4. Siapkan database

Pilih salah satu jalur berikut.

### Opsi A — impor dump (paling cepat, termasuk data demo)

Dump manual ini **tidak** membuat database dan **tidak** memakai `DROP TABLE`:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS db_wms_gudang_dty CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
mysql -u root -p db_wms_gudang_dty < database_backup/db_wms_gudang_dty.sql
```

> Database tujuan **wajib kosong**. Jika sudah memiliki tabel, kosongkan atau
> buat ulang database terlebih dahulu.

Detail isi dump dan alternatif impor ada di
[`database_backup/README.md`](../database_backup/README.md).

### Opsi B — migrasi dan seeder

```bash
php artisan migrate --seed
```

Data transaksi demo hanya dibuat saat `APP_ENV=local`. `NotifikasiDemoSeeder`
menjalankan pemeriksaan stok menipis yang sebenarnya, sehingga dump dan
seeder demo berisi 2 notifikasi nyata. Pada environment lain, seeder tetap
membuat master data dan akun default.

## 5. Build aset frontend

Untuk produksi atau sekali jalan:

```bash
npm run build
```

Untuk pengembangan dengan hot reload:

```bash
npm run dev
```

Bila muncul error “Unable to locate file in Vite manifest”, jalankan
`npm run build` atau biarkan `npm run dev` berjalan.

## 6. Jalankan aplikasi

```bash
php artisan serve
```

Atau jalankan server, queue, log, dan Vite sekaligus:

```bash
composer run dev
```

Buka `http://127.0.0.1:8000`. Root `/` otomatis redirect ke `/admin`.

## 7. Login

Halaman login panel: `http://127.0.0.1:8000/admin/login`.

Login memakai **email**, bukan username.

| Role | Email | Password |
| --- | --- | --- |
| Admin Gudang | `admin@wms.local` | `password` |
| Operator Gudang | `operator@wms.local` | `password` |
| Manajer / Supervisor | `manajer@wms.local` | `password` |

Ganti password default melalui **Pengaturan → Pengguna** setelah login
pertama.

## 8. Langkah pertama

1. Buka **Beranda** dan periksa stok aktif POY, status buffer Minggu,
   draf yang menunggu posting, grafik 30 hari, dan daftar perhatian.
2. Buka **Data Induk → Barang** dan pastikan master barang sudah sesuai.
3. Buat satu **Penerimaan POY** sebagai draf, lalu posting. Perhatikan
   stok aktif dan mutasi bertambah.
4. Buka **Persediaan → Stok Barang** dan **Laporan → Kartu Stok** untuk
   memeriksa jejak mutasi.
5. Coba **Pengeluaran POY** untuk melihat validasi stok aktif dan buffer.
6. Buka **Laporan → Rendemen** untuk melihat POY masuk vs DTY keluar.
7. Gunakan **Ctrl/⌘+K** untuk mencari data melalui global search.
8. Buka ikon notifikasi di sidebar. Demo sudah berisi 2 notifikasi stok
   menipis, termasuk Coning Oil 195 kg di bawah minimum 200 kg; notifikasi
   baru juga muncul setelah mutasi stok atau setelah menjalankan
   `php artisan wms:notifikasi-stok-menipis`.

## 9. Perintah penting

| Perintah | Kegunaan |
| --- | --- |
| `php artisan test` | Menjalankan seluruh test |
| `vendor/bin/pint` | Merapikan format kode PHP |
| `npm run build` | Build aset produksi |
| `npm run dev` | Vite development server |
| `php artisan serve` | Server lokal Laravel |
| `composer run dev` | Server + Vite + queue + log |
| `php artisan migrate --seed` | Migrasi dan data awal |
| `php artisan wms:recalculate-stock --dry-run` | Pratinjau rekalkulasi saldo mutasi |
| `php artisan schedule:work` | Menjalankan scheduler secara lokal |

## 10. Scheduler lokal

Rilis buffer Minggu dijadwalkan setiap Minggu pukul 05:45 waktu aplikasi.
Untuk menguji scheduler di lokal:

```bash
php artisan schedule:work
```

Di produksi, gunakan cron `schedule:run` seperti dijelaskan pada
[Deployment](deployment.md).
