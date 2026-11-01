# Dokumentasi WMS Gudang DTY

Dokumentasi ini disusun mengikuti kerangka **Diátaxis**: tutorial, how-to,
reference, dan explanation. Mulai dari [getting-started](getting-started.md)
bila baru pertama kali menjalankan proyek, lalu masuk ke topik yang
dibutuhkan.

## Kategori dokumentasi

| Kategori | Tujuan | Dokumen |
| --- | --- | --- |
| Tutorial | Panduan langkah demi langkah dari nol sampai bisa login | [Getting Started](getting-started.md) |
| How-to | Resep operasional untuk tugas tertentu | [Deployment](deployment.md), [Troubleshooting](troubleshooting.md), [Testing](testing.md), [Contributing](contributing.md) |
| Reference | Informasi teknis yang dapat dicari | [Features](features.md), [Database](database.md) |
| Explanation | Konsep, arsitektur, dan aturan bisnis | [Architecture](architecture.md), [Business Rules](business-rules.md) |

## Mulai cepat

1. Ikuti [Getting Started](getting-started.md) untuk menyiapkan environment.
2. Pahami [Architecture](architecture.md) dan [Business Rules](business-rules.md).
3. Lihat [Features](features.md) untuk peta modul dan URL panel.
4. Gunakan [Testing](testing.md) sebelum mengirim perubahan.
5. Ikuti [Deployment](deployment.md) saat menyiapkan server produksi.

## Peta dokumentasi

### Tutorial

- [Getting Started](getting-started.md) — prasyarat, instalasi, database,
  build, menjalankan server, login, dan langkah pertama.

### How-to

- [Deployment](deployment.md) — checklist produksi: environment, build,
  migrasi, scheduler, queue, keamanan, dan backup.
- [Troubleshooting](troubleshooting.md) — masalah umum: cache, Vite,
  login, akses role, impor SQL, notifikasi, pencarian, dan ekspor PDF.
- [Testing](testing.md) — menjalankan test, struktur suite, menambah test,
  Pint, dan build.
- [Contributing](contributing.md) — konvensi commit, branch, kualitas kode,
  dan pembaruan dokumentasi.

### Reference

- [Features](features.md) — modul, URL panel, role matrix, lifecycle status,
  dashboard, transaksi, laporan, notifikasi, pencarian, dan cetak/ekspor.
- [Database](database.md) — 26 tabel, relasi penting, enum, constraint,
  ERD ringkas, data demo, dan impor dump.

### Explanation

- [Architecture](architecture.md) — stack, layer, struktur folder, alur
  request, otorisasi, ekspor, notifikasi, widget, dan scheduler.
- [Business Rules](business-rules.md) — aturan KG, buffer Minggu, gross/netto,
  rendemen, void, opname, penomoran, dan stok non-negatif.

## Aset

Tangkapan layar berada di [`assets/`](assets/):

- [`beranda.png`](assets/beranda.png)
- [`notifikasi.png`](assets/notifikasi.png)
- [`penerimaan-poy.png`](assets/penerimaan-poy.png)
- [`rendemen.png`](assets/rendemen.png)
- [`login.png`](assets/login.png)

## Konvensi dokumentasi

- Bahasa Indonesia, istilah teknis tetap dalam bentuk aslinya.
- Setiap tautan bersifat relatif agar tetap valid di GitHub dan editor lokal.
- Dokumentasi menjelaskan perilaku aplikasi, bukan dokumen kebutuhan internal.
- Contoh perintah menggunakan path relatif dari root repositori.
