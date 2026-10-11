# WMS Gudang DTY — Sistem Manajemen Gudang Pabrik Tekstil DTY

Aplikasi berbasis web untuk mengelola keluar-masuk bahan baku **POY (Partially Oriented Yarn)**
dan produk jadi **DTY (Drawn Textured Yarn)** pada pabrik teksturisasi benang.

## Anggota Kelompok
- Apiransyah (Piran)
- Reza
- Dhafi

## Tech Stack

| Lapisan | Teknologi | Versi | Keterangan |
|---------|-----------|-------|------------|
| Backend | Laravel (PHP) | **13.35.0** (PHP ≥ 8.3) | Framework utama |
| Database | MySQL (Laragon) | — | Penyimpanan data + file `.sql` |
| Akses Data | Eloquent ORM + Migration + Seeder | — | Skema & data default |
| Frontend CSS | Bootstrap | **5.3.8** | Tampilan UI |
| Interaktif | JavaScript + Chart.js | **4.5.1** | Grafik dashboard |
| Autentikasi | Laravel Breeze | **2.4.2** | Login, register, reset password |
| Hak Akses | Kolom `role` pada tabel `users` | — | Admin / Operator / Manajer |
| Export Laporan | PDF (`barryvdh/laravel-dompdf`) + Excel (`maatwebsite/excel`) + Cetak (print browser) | — | Sesuai kebutuhan laporan |
| Build Tool | Vite | bawaan Laravel | Aset frontend |

**Catatan:** PHP minimum **8.3** (wajib untuk Laravel 13). Pastikan PHP di Laragon sudah 8.3+ sebelum `composer install`.

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
