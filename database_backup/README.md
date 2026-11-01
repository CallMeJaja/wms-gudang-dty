# Backup Database — WMS Gudang DTY

Berkas `wms_gudang_dty.sql` adalah dump struktur + data master (seed)
untuk diimpor ke Laragon (MySQL 8) atau server lain, sesuai checklist finalisasi proyek.

## Cara impor (Laragon / MySQL)

1. Buat database kosong dengan collation `utf8mb4_general_ci`:
   ```sql
   CREATE DATABASE db_wms_gudang_dty
     CHARACTER SET utf8mb4
     COLLATE utf8mb4_general_ci;
   ```
2. Impor:
   ```bash
   mysql -u root -p db_wms_gudang_dty < wms_gudang_dty.sql
   ```
3. Sesuaikan `.env` aplikasi dengan kredensial database tersebut.
4. Opsional: jalankan `php artisan migrate --force` untuk memastikan
   tidak ada migrasi yang tertinggal.

## Isi dump

- 25 tabel: skema lengkap (master, stok, transaksi, audit) + tabel
  bawaan Laravel (cache, jobs, sessions).
- 9 baris INSERT: data master hasil seeder (users, kategori, satuan,
  grade, barang, partner, konversi, nomor dokumen).
- Tidak ada data transaksi — database siap dipakai dari nol.

## Akun bawaan (password: `password`)

| Username   | Role     |
|------------|----------|
| `admin`    | Admin    |
| `operator` | Operator |
| `manajer`  | Manajer  |

> Ganti password bawaan setelah impor pertama.

## Catatan kompatibilitas

Dump dibuat dari MariaDB 13 lokal. Sudah memakai SQL standar
(utf8mb4, InnoDB, CHECK constraint) agar dapat diimpor ke MySQL 8.
Bila muncul peringatan collation `uca1400`, pastikan database tujuan
dibuat dengan `COLLATE utf8mb4_general_ci` seperti langkah 1.
