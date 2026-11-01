# Backup Database — WMS Gudang DTY

`wms_gudang_dty.sql` adalah dump siap-impor untuk aplikasi WMS Gudang DTY.
Dump memuat **26 tabel**: struktur lengkap, data master, data demo, dan
notifikasi contoh. Dump sudah menyertakan `CREATE DATABASE` dan `USE
db_wms_gudang_dty`, sehingga dapat diimpor dalam satu langkah.

## Isi dump

- **26 tabel**: master, stok, transaksi, audit, autentikasi, dan tabel
  bawaan Laravel (cache, jobs, sessions, migrations).
- **Data demo**:
  - 3 user (Admin, Operator, Manajer)
  - 9 barang
  - 62 transaksi masuk
  - 41 transaksi keluar
  - 156 mutasi stok
  - 143 activity log
  - 3 notifikasi
- Database tujuan: `db_wms_gudang_dty`
- Character set: `utf8mb4`
- Collation: `utf8mb4_general_ci`

## Import satu langkah

Karena dump sudah berisi `CREATE DATABASE` dan `USE`, cukup jalankan:

```bash
mysql -u root -p < database_backup/wms_gudang_dty.sql
```

> **Peringatan:** dump memakai `DROP TABLE`. Jangan impor ke database yang
> berisi data penting. Cadangkan database lama terlebih dahulu bila perlu.

Setelah impor, sesuaikan `.env` aplikasi:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_wms_gudang_dty
DB_USERNAME=<user-database>
DB_PASSWORD=<password-database>
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_general_ci
```

## Alternatif: buat database lalu impor

Jika ingin membuat database secara manual:

```sql
CREATE DATABASE db_wms_gudang_dty
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
```

```bash
mysql -u root -p db_wms_gudang_dty < database_backup/wms_gudang_dty.sql
```

## Alternatif tanpa dump

Gunakan migrasi dan seeder Laravel:

```bash
php artisan migrate --seed
```

Data demo transaksi dan notifikasi hanya dijalankan ketika
`APP_ENV=local`. Pada environment lain, seeder tetap membuat master data
dan akun default tanpa data transaksi demo.

## Akun default

| Role | Email | Password |
| --- | --- | --- |
| Admin Gudang | `admin@wms.local` | `password` |
| Operator Gudang | `operator@wms.local` | `password` |
| Manajer / Supervisor | `manajer@wms.local` | `password` |

Ganti password default melalui menu **Pengaturan → Pengguna** setelah
login pertama. Akun operator dipakai bersama per shift; nama operator pada
transaksi tetap diisi terpisah.

## Kompatibilitas

- MySQL 8 dan MariaDB.
- InnoDB, `utf8mb4`, dan `CHECK` constraint.
- Dump dibuat dari MariaDB dan memakai SQL standar agar dapat diimpor ke
  MySQL 8.
- Bila muncul peringatan collation `uca1400`, pastikan database tujuan
  memakai `utf8mb4_general_ci`.

## Catatan operasional

- Jangan menjalankan `php artisan migrate:fresh` pada database produksi.
- Untuk memastikan tidak ada migrasi tertinggal setelah impor:
  `php artisan migrate --force`.
- Scheduler dan queue worker bukan bagian dari dump; keduanya dikonfigurasi
  di server (lihat `docs/deployment.md`).
