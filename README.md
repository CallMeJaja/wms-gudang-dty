# WMS Gudang DTY — Sistem Manajemen Gudang Pabrik Tekstil DTY

Aplikasi berbasis web untuk mengelola keluar-masuk bahan baku **POY (Partially Oriented Yarn)**
dan produk jadi **DTY (Drawn Textured Yarn)** pada pabrik teksturisasi benang.

## Anggota Kelompok
- Apiransyah (Piran)
- Reza
- Dhafi

## Tech Stack
- Laravel 13 (PHP) + MySQL
- Bootstrap / AdminLTE

## Struktur Repository
- Root — source code aplikasi (Laravel)
- `public/` — document root aplikasi
- `database_backup/` — file database `.sql`
- `notes/` — panduan penggunaan aplikasi

## Cara Menjalankan

### Cara 1 — Laragon (Windows)
1. Clone repo ini ke folder Laragon, mis. `C:\laragon\www\wms-gudang-dty`
2. Jalankan Laragon (Apache/Nginx + MySQL)
3. Ubah **document root** virtual host ke:
   `C:\laragon\www\wms-gudang-dty\public`
4. Buka terminal di root project, lalu jalankan `composer install`
5. Copy `.env.example` → `.env`, atur koneksi database
6. Import `database_backup/wms_gudang_dty.sql` lewat phpMyAdmin/HeidiSQL
7. Buka `http://wms-gudang-dty.test` (atau sesuai domain Laragon)

### Cara 2 — Artisan Serve (semua OS)
1. `composer install`
2. `cp .env.example .env` lalu atur koneksi DB
3. Import `database_backup/wms_gudang_dty.sql`
4. `php artisan key:generate` (bila perlu)
5. `php artisan serve`
6. Buka `http://127.0.0.1:8000`

## Akun Default
| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |
| Operator | operator | operator123 |
| Manajer | manajer | manajer123 |

## Status
🚧 Dalam pengerjaan (UAS Project Manufaktur Tekstil)
