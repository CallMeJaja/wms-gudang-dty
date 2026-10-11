# Daftar Pertanyaan Wawancara — WMS Gudang DTY

| | |
|---|---|
| **Narasumber** | Pak Jamal |
| **Tujuan** | Menggali kebutuhan sistem Manajemen Gudang (POY & DTY) agar aplikasi sesuai kondisi nyata di lapangan |
| **Pewawancara** | Muhammad Apiransyah Ramdhani, Reza Asriano Maulana, Dhafi Ebsan Yurizal |
| **Tanggal** | ________________ |

> **Tips:** Rekam / minta izin untuk merekam, siapkan buku catatan, dan dokumentasikan
> dalam bentuk foto/bukti. Tandai jawaban yang memengaruhi desain database.

---

## A. Gambaran Umum & Alur Kerja Gudang

1. Bapak bisa jelaskan secara singkat alur bahan baku **POY** dari datang sampai dipakai produksi?
2. Setelah produksi menghasilkan **DTY**, produk jadi itu disimpan di mana — gudang yang sama atau berbeda?
3. Siapa saja yang punya akses ke gudang dan yang berwenang mencatat keluar-masuk barang?
4. Berapa banyak jenis bahan baku dan produk jadi yang biasanya disimpan? (perkiraan jumlah SKU)
5. Apakah ada gudang/bagian terpisah untuk bahan baku, produk jadi, dan barang reject/sisa?

---

## B. Master Data (Data Induk)

6. Data apa saja yang perlu dicatat untuk setiap **bahan baku POY**? (kode, nama, jenis/denier, warna, satuan, dll)
7. Sama untuk **produk jadi DTY** — data apa yang harus ada?
8. Bagaimana kode barang dibuat? Apakah sudah ada format standar atau bebas?
9. **Satuan** yang dipakai: POY/DTY dihitung dalam Kg, Cone, Box, atau lainnya? Bisa beberapa satuan sekaligus?
10. Apakah ada **"item pendukung lainnya"** yang perlu masuk master data? (misal: kardus, palet, plastik packing)
11. Data **supplier** apa saja yang dicatat? (nama, kontak, alamat, jenis barang yang dipasok)
12. Apakah ada **data pelanggan** yang perlu disimpan di sistem gudang, atau itu urusan bagian pengiriman?

---

## C. Transaksi Barang Masuk (Penerimaan)

13. Apa saja sumber barang masuk? (dari supplier, dari produksi, retur pelanggan?)
14. Data apa saja yang dicatat saat penerimaan? (tanggal, nomor surat jalan, supplier, jumlah, batch/lot, kondisi barang)
15. Apakah ada pencatatan **batch / lot / nomor rol**? Kalau ada, apakah perlu dilacak sampai ke produksi?
16. Apakah barang masuk kadang **tidak sesuai pesanan** (kurang/lebih/rusak)? Bagaimana penanganannya?
17. Apakah penerimaan selalu diverifikasi oleh orang tertentu? Perlu tanda tangan/approval di sistem?

---

## D. Transaksi Barang Keluar (Pengeluaran)

18. Barang keluar dari gudang untuk keperluan apa saja? (produksi, pengiriman ke pelanggan, retur ke supplier)
19. Data apa saja yang dicatat saat pengeluaran? (tanggal, tujuan, barang, jumlah, siapa yang ambil)
20. Apakah pengeluaran POY untuk produksi perlu dikaitkan dengan **mesin tertentu** atau **shift tertentu**?
21. Apakah ada batas maksimal pengambilan per permintaan, atau approval dari atasan?

---

## E. Stok & Notifikasi

22. Bagaimana cara memantau stok saat ini — apakah masih manual (buku/Excel)?
23. Apakah setiap barang punya **stok minimum**? Berapa contohnya?
24. Jika stok menipis, siapa yang perlu diberi tahu dan bagaimana? (notifikasi aplikasi, email, WhatsApp?)
25. Apakah stok fisik sering **tidak cocok** dengan catatan? Apa penyebab paling umum? (opname?)
26. Apakah perlu fitur **stok opname / koreksi stok**?

---

## F. Laporan & Ekspor

27. Laporan apa saja yang rutin dibutuhkan manajemen? (laporan harian, bulanan, kartu stok, penggunaan bahan baku vs output)
28. Format laporan yang diinginkan seperti apa? Apakah ada contoh laporan lama yang bisa kami lihat?
29. Laporan biasanya dipakai untuk apa / diserahkan ke siapa?
30. Untuk ekspor, format apa yang lebih sering dipakai: **PDF, Excel, atau cetak langsung**?
31. Perlu **filter** apa saja pada laporan? (rentang tanggal, barang, supplier, jenis)

---

## G. User, Hak Akses & Keamanan

32. Siapa saja yang akan memakai aplikasi ini? Sebutkan **peran/jabatan** masing-masing.
33. Untuk tiap peran, apa yang boleh dan tidak boleh dilakukan?
    - **Admin:** ________________
    - **Operator Gudang:** ________________
    - **Manajer / atasan:** ________________
34. Apakah setiap transaksi perlu tercatat **siapa yang menginput** (log aktivitas)?
35. Apakah ada kebutuhan **approval berjenjang** untuk transaksi tertentu?

---

## H. Kondisi Teknis & Implementasi

36. Perangkat apa yang akan dipakai untuk menjalankan aplikasi? (PC gudang, laptop, tablet, HP?)
37. Apakah aplikasi dijalankan **offline di dalam pabrik (LAN)** atau perlu diakses dari luar/online?
38. Apakah nanti perlu **scan barcode / QR code** untuk barang?
39. Berapa banyak orang yang akan mengakses aplikasi secara bersamaan?
40. Apakah sudah ada sistem lain (produksi, absensi, pengiriman) yang datanya perlu terhubung?

---

## I. Konfirmasi Ulang (Asumsi yang Perlu Dipastikan)

> Bagian ini untuk memastikan asumsi tim saat merancang sistem sudah benar.

41. Kami berasumsi aplikasi ini **berbasis web** (dibuka lewat browser). Apakah sesuai keinginan Bapak?
42. Kami rencananya memakai **3 role: Admin, Operator Gudang, Manajer**. Apakah sudah sesuai struktur di lapangan?
43. Kami berasumsi **POY = bahan baku masuk**, **DTY = produk jadi**. Apakah pemahaman ini benar?
44. Kami berasumsi laporan "penggunaan bahan baku terhadap output produksi" artinya
    **berapa KG POY terpakai dibanding berapa KG DTY yang dihasilkan**. Apakah benar?
45. Apakah ada **istilah khusus** di perusahaan yang harus kami pakai di aplikasi supaya
    karyawan tidak bingung? (misal nama gudang, nama proses, kode internal)
46. Apakah aplikasi perlu memakai **logo / identitas perusahaan**? Sudah ada brand/namanya?
47. Kira-kira **siapa** yang nanti akan menguji / memakai hasil project ini saat presentasi?

---

## J. Penutup

48. Kalau ada satu hal yang paling Bapak ingin aplikasi ini selesaikan, apa itu?
49. Apakah ada contoh laporan / form / dokumen yang bisa kami jadikan acuan desain?
50. Boleh kami menghubungi Bapak lagi jika ada pertanyaan lanjutan saat pengerjaan?

---

## Catatan Setelah Wawancara

- **Kesimpulan jawaban penting:** ________________
- **Asumsi yang perlu direvisi:** ________________
- **Fitur tambahan yang muncul:** ________________
- **Bukti dokumentasi (foto/rekaman):** ________________
