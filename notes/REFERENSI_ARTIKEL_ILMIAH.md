# Referensi & Kajian Pustaka — WMS Gudang DTY

Kumpulan artikel ilmiah dan sumber referensi sebagai **dasar teori** pengembangan
Sistem Manajemen Gudang (WMS) untuk pabrik tekstil DTY.

**Kelompok:** Muhammad Apiransyah Ramdhani · Reza Asriano Maulana · Dhafi Ebsan Yurizal

> **Cara memakai dokumen ini:** jadikan acuan saat menulis latar belakang / tinjauan pustaka,
> dan kutip pada laporan UAS. Tandai (✅) referensi yang sudah benar-benar dibaca.

---

## A. Warehouse Management System (WMS)

| Status | Judul | Penulis / Tahun | Sumber | Keterkaitan |
|--------|-------|-----------------|--------|-------------|
| ✅ | Perancangan Sistem Informasi Gudang Berbasis Web | D. A. Susanto, 2023 | JATISI (Jurnal Teknik Informatika dan Sistem Informasi), Jurnal MDP | Contoh rancangan WMS web; sebelumnya pakai Excel → relevan dengan masalah gudang manual |
| ✅ | Sistem Informasi Gudang Berbasis Web untuk Manajemen Persediaan | F. Baihaqqi, 2023 | Jurnal JATI, ITN Malang | Studi kasus penerapan gudang berbasis web pada perusahaan |
| ✅ | Rancang Bangun Sistem Informasi Manajemen Pergudangan | 2023 | JEISBI, Universitas Negeri Surabaya | Metodologi rancang bangun sistem pergudangan |
| ✅ | Perancangan Sistem Informasi Manajemen Gudang Berbasis Web pada Cozy Mart | K. Prandiska, 2025 | IT-Explore, UKSW | Referensi terbaru; alur perancangan manajemen gudang |
| ✅ | Factors Influencing the Successful Implementation of WMS | 2020 | IJCA (International Journal of Computer Applications) | Faktor kunci keberhasilan implementasi WMS (teori pendukung) |
| ✅ | Evaluation of the Warehouse Management System | 2024 | IIETA | Manfaat & benefit implementasi WMS (cross-docking, real-time) |

---

## B. Sistem Informasi Persediaan / Bahan Baku (Pabrik Tekstil)

| Status | Judul | Penulis / Tahun | Sumber | Keterkaitan |
|--------|-------|-----------------|--------|-------------|
| ✅ | Sistem Informasi Persediaan Bahan Baku Produksi Berbasis Web | R. C. E. Nursumaryanti, 2023 | Jurnal JRIS, Universitas Swadarma | Sangat relevan: persediaan bahan baku produksi berbasis web |
| ✅ | Sistem Informasi Manajemen Persediaan Bahan Baku dengan Metode EOQ & JIT Berbasis Web | M. A. Panjaitan, 2025 | Jurnal Saintek, Univ. Banten Jaya | Referensi pengendalian persediaan (metode EOQ/JIT) + notifikasi stok |
| ✅ | Sistem Informasi Persediaan untuk Monitoring Bahan Baku | K. Purwantini, 2022 | Prosiding SEMNASTEKMU | Monitoring persediaan bahan baku |
| ✅ | Sistem Informasi Manajemen Gudang Bahan Baku (PHP + MySQL + Barcode) | 2026 | Jurnal Cendekia (Jurnal P4I) | Teknologi sama (PHP, MySQL), tambahan barcode |
| ✅ | Sistem Informasi Persediaan Bahan Baku (Pencatatan & Pemantauan) | F. S. Jati, 2024 | Jurnal Innovative | Fitur pencatatan, pemantauan, pengelolaan stok |
| ✅ | Sistem Informasi Persediaan Barang Berbasis Web (Bootstrap + MySQL) | T. Rahmawati, 2024 | Jurnal SMATIKA, Univ. Binus | Contoh pemakaian Bootstrap + MySQL; fitur login & pengelolaan data |
| ✅ | Sistem Informasi Manajemen Inventaris Berbasis Lokasi | E. S. E. Saputra, 2024 | Jurnal JIAJ, Universitas Atma Jaya Yogyakarta | Inventori berbasis lokasi & pencatatan transaksi |

---

## C. Konteks Industri: DTY / Proses Teksturisasi Benang

| Status | Judul | Penulis / Tahun | Sumber | Keterkaitan |
|--------|-------|-----------------|--------|-------------|
| ✅ | Real-Time Identification of Yarn Irregularities on DTY Machine Through Vibration Monitoring | 2026 | ResearchGate | Konteks mesin DTY & monitoring produksi (latar belakang industri) |
| ✅ | Investigation of the Relationship Between Draw-Textured Yarn Structure and Fabric Performance | J. H. Emon, 2026 | SAGE Journals | Karakteristik hasil DTY (kualitas, kekuatan, cacat) |
| ✅ | Production Process for Improving Full-Dull DTY Quality | — | Google Patents (CN111647992A) | Proses produksi DTY (pemahaman domain bisnis) |
| ✅ | The Influence of Warehouse Management Systems on Supply Chain Efficiency (Textile/Garment) | 2023 | ResearchGate | Dampak WMS pada rantai pasok industri tekstil/garmen |

> **Catatan domain:** POY (*Partially Oriented Yarn*) = bahan baku, DTY (*Drawn Textured Yarn*)
> = produk jadi hasil proses teksturisasi. Referensi di bagian C memperkuat pemahaman istilah
> dan alur produksi ini agar sistem sesuai kondisi nyata.

---

## D. Teknologi Pengembangan (Laravel, MySQL, Bootstrap)

| Status | Sumber | Keterkaitan |
|--------|--------|-------------|
| ✅ | Dokumentasi resmi Laravel — https://laravel.com/docs | Framework backend utama |
| ✅ | Dokumentasi resmi MySQL — https://dev.mysql.com/doc | Basis data relasional |
| ✅ | Dokumentasi resmi Bootstrap 5 — https://getbootstrap.com/docs | Framework UI |
| ✅ | Laravel Breeze — https://laravel.com/docs/starter-kits | Autentikasi (login, register) |
| ✅ | barryvdh/laravel-dompdf — https://github.com/barryvdh/laravel-dompdf | Export laporan PDF |
| ✅ | maatwebsite/excel — https://docs.laravel-excel.com | Export laporan Excel |
| ✅ | Tailwind/Chart.js — https://www.chartjs.org/docs | Grafik pada dashboard |

---

## E. Ringkasan Dasar yang Diambil (draft untuk latar belakang laporan)

> Beberapa penelitian menunjukkan bahwa pencatatan persediaan yang masih manual (buku/Excel)
> menyebabkan ketidakcocokan data stok dan lambatnya penyusunan laporan. Penerapan sistem
> informasi gudang berbasis web terbukti mempercepat pengolahan data persediaan, meminimalkan
> kesalahan pencatatan, dan menyediakan informasi stok secara akurat dan *real-time*
> (Susanto, 2023; Nursumaryanti, 2023; Jati, 2024). WMS juga meningkatkan efisiensi rantai
> pasok pada industri tekstil/garmen (2023). Berdasarkan hal tersebut, penelitian ini merancang
> Sistem Manajemen Gudang berbasis web untuk pabrik tekstil DTY menggunakan Laravel dan MySQL.

*(Isi & nomor kutipan disesuaikan lagi saat penulisan laporan akhir.)*

---

## F. Format Sitasi (APA 7)

Contoh:

> Susanto, D. A. (2023). Perancangan sistem informasi gudang berbasis web. *JATISI (Jurnal
> Teknik Informatika dan Sistem Informasi)*, 9(4), 1–12.

> Nursumaryanti, R. C. E. (2023). Sistem informasi persediaan bahan baku produksi berbasis web.
> *Jurnal JRIS*, 2(1), 1–10.

*(Sesuaikan volume, nomor, dan halaman dengan artikel aslinya saat mengambil dari jurnal.)*
