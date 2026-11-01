# Business Rules

Aturan pada dokumen ini adalah kontrak perilaku aplikasi. Nilai angka
operasional berada di `config/wms.php`; jangan menulis ulang angka tersebut
di kode atau dokumentasi lain tanpa memperbarui konfigurasi.

## 1. KG adalah single source of truth

- Saldo stok dan seluruh mutasi dihitung dari **berat bersih (KG)** hasil
  timbangan atau dokumen.
- Box, bobbin, dan pallet adalah satuan kemasan fisik untuk input dan
  tampilan, bukan dasar saldo.
- Konversi kemasan hanya membantu pengisian atau validasi silang.
- Tidak ada saldo yang dihitung dari perkalian jumlah kemasan.

## 2. POY tidak dikonversi dari bobbin

- Berat netto POY wajib diisi manual.
- Bila berat KG kosong atau nol, transaksi ditolak meskipun bobbin diisi.
- Tidak ada konversi otomatis bobbin ke KG untuk POY.
- Bobbin pada POY hanya atribut pelengkap.

Alasan: berat per bobbin dapat berbeda menurut denier dan kondisi aktual,
sehingga saldo harus mengikuti timbangan.

## 3. DTY: box, bobbin, dan KG

- Jumlah box dapat mengisi bobbin, netto, dan gross secara otomatis.
- Nilai otomatis dapat diubah manual oleh operator.
- Bobbin adalah turunan box; KG tetap menjadi dasar saldo.
- Gross dan netto dicatat terpisah.
- Toleransi gross per box berbeda menurut grade dan bersifat informatif;
  peringatan tidak memblokir transaksi.

## 4. Pallet hanya indikator visual

- Pallet dipakai untuk ambang tampilan dan status buffer.
- Pallet tidak dipakai untuk menambah atau mengurangi saldo.
- Acuan berat per pallet berbeda antara POY dan DTY.
- Konversi pallet bukan bagian dari akuntansi stok.

## 5. Stok aktif dan buffer

Setiap posisi stok memiliki dua saldo:

| Pos | Makna |
| --- | --- |
| `AKTIF` | Stok bebas dipakai pada hari kerja |
| `BUFFER` | Stok yang disisihkan untuk hari Minggu |

- Pemisahan pos hanya berlaku untuk **POY**.
- DTY dan item pendukung selalu masuk `AKTIF`; saldo buffer tetap nol.
- Total stok = `stok_aktif` + `stok_buffer`.
- Pengeluaran POY hanya memotong `stok_aktif`.
- Buffer tidak boleh dipakai untuk pengeluaran pada hari kerja.

## 6. Buffer Minggu

Buffer Minggu mengikuti aturan berikut:

1. Hanya untuk barang POY dengan penanda `is_merge_minggu = true`.
2. Hanya diproses pada hari kerja Senin–Sabtu. Hari Minggu tidak mengisi
   buffer karena buffer justru dirilis.
3. Pasokan POY hari itu dihitung dari seluruh transaksi yang sudah
   memengaruhi stok pada tanggal tersebut, bukan hanya satu dokumen.
4. Bila pasokan hari itu **kurang dari 7 pallet**, buffer **dilewati**
   seluruhnya. Tidak ada pemotongan proporsional. Baris alokasi tetap
   dicatat dengan penanda `dilewati = true`.
5. Bila pasokan mencapai ambang, sisihkan **1 pallet per hari kerja** ke
   buffer; sisanya tetap aktif.
6. Total buffer gudang lintas semua merge dibatasi **6 pallet**. Bila sisa
   kuota kurang dari satu pallet, hanya sebesar sisa kuota yang disisihkan.
7. Satu kombinasi barang, merge, dan tanggal hanya boleh memiliki satu
   baris alokasi.
8. Alokasi dan rilis buffer tidak mengubah total stok; hanya memindahkan
   pos. Setiap perpindahan menulis dua baris mutasi: keluar dari pos asal
   dan masuk ke pos tujuan.

Rilis buffer:

- Dijadwalkan otomatis setiap **Minggu pukul 05:45** waktu aplikasi.
- Dapat dipicu manual dari Beranda pada hari Minggu oleh Admin atau
  Operator.
- Seluruh saldo buffer untuk POY merge Minggu dipindahkan ke stok aktif.
- Rilis bersifat idempoten: buffer nol tidak menghasilkan error.

## 7. Gross dan netto

- Netto adalah dasar pengurangan dan penambahan stok.
- Gross dipakai untuk pemeriksaan fisik dan toleransi kemasan DTY.
- Pada penerimaan DTY, gross disimpan bersama keterangan batch agar tetap
  dapat diperiksa tanpa mengubah skema.
- Toleransi gross per grade adalah alat bantu, bukan gerbang pemblokir.
- POY dan item pendukung tidak memerlukan gross.

## 8. Rendemen POY vs DTY

- Rendemen membandingkan **POY yang keluar ke produksi** dengan **DTY yang
  masuk dari produksi** pada periode yang sama.
- Hanya transaksi berstatus `POSTED` yang dihitung.
- Pengelompokan memakai merge POY asal pada penerimaan DTY.
- Rumus: `rendemen = DTY / POY x 100%`; `waste = 100% - rendemen`.
- Bila POY nol, rendemen dan waste kosong.
- Rendemen adalah indikator efisiensi massa, bukan konversi teknis.
- WIP diabaikan dan denier POY dapat berbeda dari DTY, sehingga hasil bisa
  sedikit di atas atau di bawah 100%.
- Disclaimer wajib tampil pada halaman dan ekspor rendemen.

## 9. Void transaksi

- Hanya Admin yang boleh melakukan void.
- Alasan void wajib diisi, minimal 10 karakter.
- Void tidak menghapus transaksi; status berubah menjadi `VOID` dan alasan
  disimpan.
- Void penerimaan membalik mutasi:
  1. Bila buffer terkait belum dirilis, buffer dikembalikan ke aktif.
  2. Stok aktif dikurangi sebesar total penerimaan.
- Void penerimaan ditolak bila stok aktif tidak cukup karena sebagian
  sudah dipakai produksi.
- Void pengeluaran menambah kembali stok aktif sebesar total netto.
- Baris alokasi buffer tidak dihapus; hanya diberi penanda pembatalan.
- Void ganda dicegah dengan lock dan pemeriksaan status di dalam transaksi.

## 10. Stok opname

- Hanya Admin.
- Stok sistem = stok aktif + stok buffer.
- Stok fisik tidak boleh negatif.
- Satu posisi barang, merge, dan grade hanya boleh diopname sekali per
  tanggal.
- Selisih positif membuat transaksi penyesuaian masuk.
- Selisih negatif membuat transaksi penyesuaian keluar; pengurangan diambil
  dari aktif terlebih dahulu, lalu dari buffer bila perlu.
- Selisih nol tetap dicatat sebagai hasil opname tanpa transaksi
  penyesuaian.
- Kolom selisih dihitung oleh database (generated stored).

## 11. Penomoran dokumen atomik

- Setiap jenis dokumen memiliki counter per periode di `nomor_dokumen`.
- Unique `(jenis, periode)` mencegah baris ganda.
- Nomor dibuat di dalam transaksi dengan `lockForUpdate()`, sehingga dua
  request paralel tidak mendapat nomor yang sama.
- Format nomor berasal dari konfigurasi, bukan string hardcode.
- Pratinjau nomor tidak menaikkan counter.
- Baris counter harus tersedia untuk setiap jenis dokumen; seeder
  menyiapkan periode berjalan.

## 12. Stok tidak boleh negatif

- Service menolak pengurangan melebihi saldo pos.
- Database juga memiliki `CHECK` yang memastikan `stok_aktif` dan
  `stok_buffer` tidak negatif.
- Bila stok aktif tidak cukup, pengeluaran ditolak dan seluruh transaksi
  dibatalkan.
- Buffer tidak boleh dipakai sebagai jalan keluar untuk menutupi
  kekurangan stok aktif pada hari kerja.

## 13. Konsistensi kategori, grade, dan merge

- Jenis transaksi menentukan kategori barang yang boleh dipakai:
  - Penerimaan/pengeluaran POY hanya untuk barang POY.
  - Penerimaan/pengeluaran DTY hanya untuk barang DTY.
  - Gate Pass hanya untuk item pendukung.
- Grade wajib berasal dari kategori yang sama dengan barang.
- Merge wajib diisi untuk setiap mutasi stok.
- Barang nonaktif tidak boleh dipakai pada transaksi baru.
- Satu baris detail mewakili satu kombinasi barang, merge, dan grade.

## 14. Status dan pengaruhnya terhadap stok

- `DRAFT` belum mengubah stok dan belum menulis mutasi.
- `POSTED` dan `ISSUED` sudah mengubah stok dan menulis mutasi.
- `VOID` dan `CANCELLED` adalah status final.
- Dokumen resmi (Gate Pass dan Surat Jalan) memakai `ISSUED`.
- Transaksi internal memakai `POSTED`.
- Hanya draf yang boleh diubah atau dihapus.
- Dokumen yang sudah diterbitkan tidak boleh diedit; gunakan void atau
  pembatalan.

## 15. Aturan role

- Admin memiliki akses penuh, termasuk master data, void, opname, pengguna,
  dan riwayat aktivitas.
- Operator dapat membuat dan mem-posting transaksi serta merilis buffer,
  tetapi tidak dapat mengubah master, void, opname, atau pengguna.
- Manajer bersifat read-only dan dapat melihat laporan.
- Hanya user aktif yang dapat mengakses panel.
- Pengguna tidak dihapus; gunakan status nonaktif.

## 16. Audit trail

- `activity_logs` bersifat append-only.
- Login dan logout dicatat otomatis.
- Create, update, delete, void, dan rilis buffer dicatat oleh service atau
  resource terkait.
- Log menyimpan user, modul, referensi, keterangan, alamat IP, user agent,
  dan waktu.
- Log tidak boleh diubah atau dihapus dari panel.
