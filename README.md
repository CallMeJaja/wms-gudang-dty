# WMS Gudang DTY — Sistem Manajemen Gudang Pabrik Tekstil DTY

Aplikasi berbasis web untuk mengelola keluar-masuk bahan baku **POY (Partially Oriented Yarn)**
dan produk jadi **DTY (Drawn Textured Yarn)** pada pabrik teksturisasi benang.

## Deskripsi Project

Gudang menyimpan bahan baku **POY (Partially Oriented Yarn)** sebagai bahan utama proses
teksturisasi. Sistem ini mencatat **keluar-masuk bahan baku**, memberikan **notifikasi ketika
stok menipis**, serta membuat **laporan penggunaan bahan baku terhadap output produksi**.

Project ini merupakan bagian dari rangkaian sistem terintegrasi pabrik tekstil DTY, dengan fokus
pada pengelolaan persediaan gudang — mulai dari penerimaan barang dari supplier, pengeluaran
untuk kebutuhan produksi, hingga pemantauan stok dan pelaporannya.

## Tujuan

Mengatur keluar-masuk bahan baku (POY) dan produk jadi (DTY) secara terstruktur, sehingga
ketersediaan stok dapat dipantau secara akurat dan mendukung kelancaran proses produksi.

## Fitur Utama

- **Beranda** — ringkasan stok aktif, status buffer Minggu beserta aksi rilis buffer,
  grafik stok 30 hari, ringkasan per kategori, dan alert stok menipis.
- **Master data** — Barang, Partner, Grade, Kategori Barang, Satuan, dan Konversi Satuan.
- **Transaksi masuk** — Penerimaan POY, Gate Pass Pendukung, dan Penerimaan DTY.
- **Transaksi keluar** — Pengeluaran POY, Surat Jalan DTY, dan Pengeluaran Scrap.
- **Stok** — Stok Barang dan Stok Opname.
- **Laporan** — Keluar-Masuk, Kartu Stok, dan Rendemen, dengan ekspor Excel/PDF serta cetak.
- **Pengguna dan activity log** — manajemen akun dan jejak aktivitas pengguna.
- **Cetak dokumen** — Gate Pass dan Surat Jalan siap cetak.

### Alur Bisnis Inti

```
Supplier ──masuk──▶ [ GUDANG : stok POY naik ]
                        │
Produksi ambil POY ◀──keluar── [ stok POY turun ]
                        │
Produksi hasilkan DTY ──masuk──▶ [ stok DTY naik ]
                        │
DTY keluar untuk dikirim ──keluar──▶ [ stok DTY turun ]
```

## Hak Akses (RBAC)

| Role | Kewenangan |
|------|------------|
| **Admin Gudang** | Akses penuh: master data, transaksi, void transaksi, stok opname, dan manajemen pengguna. |
| **Operator Gudang** | Input transaksi masuk/keluar dan operasi gudang sehari-hari. |
| **Manajer / Supervisor** | Akses baca (read-only) dan laporan. |

## Anggota Kelompok
- Muhammad Apiransyah Ramdhani
- Reza Asriano Maulana
- Dhafi Ebsan Yurizal

## Tech Stack

| Lapisan | Teknologi | Versi Terpasang | Keterangan |
|---------|-----------|-----------------|------------|
| Backend | Laravel (PHP) | **13.35.0** (PHP ≥ 8.3) | Framework utama |
| UI / Admin Panel | Filament | **5.11.0** | Seluruh antarmuka aplikasi di `/admin` |
| Reaktivitas | Livewire | **4.4.8** | Komponen dinamis panel Filament |
| Database | MySQL | **8** | Penyimpanan data + file `.sql` |
| Akses Data | Eloquent ORM + Migration + Seeder | — | Skema & data default |
| Hak Akses | Policy + kolom `role` pada tabel `users` | — | Admin / Operator / Manajer |
| Export Laporan | `barryvdh/laravel-dompdf` | **3.1.2** | Ekspor PDF |
| Export Laporan | `maatwebsite/excel` | **4.0.6** | Ekspor Excel |
| Testing | PHPUnit | **12.5.38** | Test otomatis |
| Code Style | Laravel Pint | **1.32.1** | Formatter PHP |
| Build Tool | Vite + `laravel-vite-plugin` | **8.3.5** / **3.2.0** | Aset frontend |

**Catatan:** PHP minimum **8.3** (wajib untuk Laravel 13). Grafik dashboard menggunakan widget
chart bawaan Filament, bukan library chart terpisah.

## Struktur Repository

```
app/
├── Filament/          # Seluruh UI panel admin (Resources, Pages, Widgets)
├── Services/          # Logika bisnis (stok, buffer, penerimaan, pengeluaran, laporan)
├── Policies/          # Aturan otorisasi per model
├── Exports/           # Kelas ekspor Excel (Keluar-Masuk, Kartu Stok, Rendemen)
└── Models/            # Model Eloquent
database/
├── migrations/        # Skema database
└── seeders/           # Data awal (user, master data, demo transaksi)
database_backup/       # Backup database `.sql` (wms_gudang_dty.sql)
tests/                 # Test PHPUnit (Feature & Unit)
```

Folder `notes/` **tidak disertakan** di repository ini. Dokumen PRD tersedia terpisah
(lokal/backup), bukan bagian dari repo.

## Cara Menjalankan

### Requirement
- PHP **≥ 8.3** beserta ekstensi umum Laravel (openssl, pdo, mbstring, tokenizer, xml, ctype, json, bcmath)
- Composer
- Node.js **20+** dan npm
- MySQL **8**

### Langkah
1. `composer install`
2. `npm install`
3. `cp .env.example .env` lalu sesuaikan koneksi database (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)
4. `php artisan key:generate`
5. Siapkan database, pilih salah satu:
   - Import `database_backup/wms_gudang_dty.sql`, **atau**
   - `php artisan migrate --seed` untuk membuat skema dan data awal dari nol
6. `npm run build` (produksi) atau `npm run dev` (pengembangan)
7. `php artisan serve`
8. Buka `http://127.0.0.1:8000/admin`

Root `/` otomatis redirect ke `/admin`. Rute `/dashboard` dan `/login` juga redirect ke panel
Filament. Login panel menggunakan **email**.

## Akun Default

Tersedia setelah menjalankan seeder (`php artisan migrate --seed`).

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@wms.local` | `password` |
| Operator | `operator@wms.local` | `password` |
| Manajer | `manajer@wms.local` | `password` |

**Catatan keamanan:** ganti password default melalui menu **Pengaturan → Pengguna** setelah
login pertama.

## Testing & QA

```bash
php artisan test       # seluruh test (197 test lulus)
vendor/bin/pint        # perbaiki format kode PHP
npm run build          # build aset frontend
```

## Status

Implementasi antarmuka Filament **sudah selesai** dan saat ini berada pada tahap
**finalisasi dan QA** (UAS Project Manufaktur Tekstil).
