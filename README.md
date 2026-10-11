# WMS Gudang DTY — Sistem Manajemen Gudang Pabrik Tekstil DTY

Aplikasi berbasis web untuk mengelola keluar-masuk bahan baku **POY (Partially Oriented Yarn)**
dan produk jadi **DTY (Drawn Textured Yarn)** pada pabrik teksturisasi benang.

## Anggota Kelompok
- Apiransyah (Piran)
- Reza
- Dhafi

## Tech Stack
- Laravel (PHP) + MySQL
- Bootstrap / AdminLTE

## Struktur Repository
- `web/` — source code aplikasi (Laravel)
- `database/` — file database `.sql`
- `notes/` — panduan penggunaan aplikasi

## Cara Menjalankan (Development)
1. Clone repo ini
2. Masuk ke folder `web/`, jalankan `composer install`
3. Copy `.env.example` menjadi `.env`, lalu atur koneksi database
4. Import file `database/wms_gudang_dty.sql`
5. Jalankan `php artisan key:generate` bila perlu
6. Jalankan `php artisan serve`
7. Buka `http://127.0.0.1:8000`

## Akun Default
| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |
| Operator | operator | operator123 |
| Manajer | manajer | manajer123 |

## Status
🚧 Dalam pengerjaan (UAS Project Manufaktur Tekstil)
