# Features

Seluruh antarmuka aplikasi berada di panel `/admin`. Root `/` redirect ke
`/admin`, dan login memakai email.

![Beranda WMS Gudang DTY](assets/beranda.png)

## Peta modul

| Grup | Menu | URL | Keterangan |
| --- | --- | --- | --- |
| Beranda | Beranda | `/admin` | Ringkasan operasional dan aksi cepat |
| Data Induk | Barang | `/admin/barangs` | Master barang POY, DTY, dan pendukung |
| Data Induk | Mitra | `/admin/partners` | Supplier, customer, dan partner internal |
| Data Induk | Grade | `/admin/grades` | Grade per kategori |
| Data Induk | Kategori | `/admin/kategori-barangs` | Kategori POY, DTY, PENDUKUNG |
| Data Induk | Satuan | `/admin/satuans` | KG, BOX, PALLET, PCS, ROLL, BOBBIN |
| Data Induk | Konversi | `/admin/konversi-satuans` | Faktor konversi satuan |
| Penerimaan | POY | `/admin/penerimaan-poy` | Penerimaan bahan baku POY |
| Penerimaan | Gate Pass | `/admin/penerimaan-pendukung` | Item pendukung dengan dokumen resmi |
| Penerimaan | DTY | `/admin/penerimaan-dty` | Produk jadi dari area produksi |
| Pengeluaran | POY | `/admin/pengeluaran-poy` | Pengeluaran POY ke produksi |
| Pengeluaran | Surat Jalan | `/admin/surat-jalan-dty` | Pengiriman DTY |
| Pengeluaran | Scrap / Reject | `/admin/pengeluaran-scrap` | Scrap dan reject DTY |
| Persediaan | Stok Barang | `/admin/stok-barangs` | Posisi stok aktif dan buffer |
| Persediaan | Stok Opname | `/admin/stok-opnames` | Penyesuaian hasil hitung fisik |
| Laporan | Keluar-Masuk | `/admin/laporan/keluar-masuk` | Arus mutasi per periode |
| Laporan | Kartu Stok | `/admin/laporan/kartu-stok` | Saldo berjalan per posisi |
| Laporan | Rendemen | `/admin/laporan/rendemen` | Rekonsiliasi POY vs DTY |
| Pengaturan | Pengguna | `/admin/users` | Manajemen akun |
| Pengaturan | Riwayat Aktivitas | `/admin/activity-logs` | Audit trail |

## Role matrix

| Kemampuan | Admin | Operator | Manajer |
| --- | --- | --- | --- |
| Melihat dashboard dan laporan | Ya | Ya | Ya |
| Melihat master data | Ya | Ya | Ya |
| Mengubah master data | Ya | Tidak | Tidak |
| Membuat dan mengubah draf transaksi | Ya | Ya | Tidak |
| Posting/issue transaksi | Ya | Ya | Tidak |
| Void transaksi | Ya | Tidak | Tidak |
| Stok opname | Ya | Tidak | Tidak |
| Mengelola pengguna | Ya | Tidak | Tidak |
| Melihat riwayat aktivitas | Ya | Tidak | Tidak |
| Melihat stok barang | Ya | Ya | Ya |
| Ekspor dan cetak laporan | Ya | Ya | Ya |

Manajer adalah role read-only. Operator memakai akun bersama per shift;
nama operator pada transaksi diisi manual.

## Lifecycle status

```mermaid
stateDiagram-v2
    [*] --> DRAFT
    DRAFT --> POSTED: transaksi internal
    DRAFT --> ISSUED: dokumen resmi
    POSTED --> VOID
    ISSUED --> CANCELLED
    ISSUED --> VOID
    VOID --> [*]
    CANCELLED --> [*]
```

- `DRAFT` belum mengubah stok.
- `POSTED` dan `ISSUED` sudah memengaruhi stok.
- `VOID` dan `CANCELLED` adalah status final.
- Dokumen resmi adalah Gate Pass dan Surat Jalan DTY; keduanya memakai
  status `ISSUED`.
- Transaksi internal memakai `POSTED`.
- Void tidak menghapus data; mutasi dibalik dan alasan disimpan.

## Beranda

Beranda menampilkan:

- **Stok aktif POY** dalam kg dan pallet, dengan indikator aman atau
  risiko stop produksi.
- **Buffer Minggu** dalam pallet, status siap/belum siap/terlambat, dan
  aksi rilis buffer pada hari Minggu.
- **Draf menunggu posting** untuk penerimaan dan pengeluaran.
- **Grafik 30 hari** untuk POY dan DTY masuk/keluar.
- **Perlu perhatian** berisi stok di bawah minimum, stok aktif kritis,
  dan buffer yang terlambat.

Aksi **Rilis Buffer Minggu** tersedia bagi Admin dan Operator ketika hari
Minggu dan masih ada buffer.

## Data Induk

- **Barang** — kode, nama, kategori, denier, filament, satuan dasar,
  stok minimum, dan penanda merge Minggu.
- **Mitra** — supplier, customer, gabungan, atau internal.
- **Grade** — unik per kategori; grade scrap tetap dipertahankan.
- **Kategori** — POY, DTY, dan item pendukung.
- **Satuan** — satuan dasar dan satuan kemasan.
- **Konversi** — faktor dari satuan kemasan ke KG atau satuan lain.

Perubahan master data hanya dilakukan Admin. Operator dan Manajer dapat
melihat data.

## Penerimaan

### Penerimaan POY

![Penerimaan POY](assets/penerimaan-poy.png)

- Berat masuk wajib diisi manual dalam KG hasil timbangan.
- Bobbin dan box hanya atribut pelengkap, bukan dasar saldo.
- Satu baris mewakili kombinasi barang, merge, dan grade.
- Setelah posting, stok aktif bertambah dan buffer dapat dialokasikan
  sesuai aturan Minggu.

### Gate Pass Pendukung

- Untuk paper tube, carton box, oli, pallet, dan item pendukung lain.
- Memakai lifecycle dokumen resmi sampai `ISSUED`.
- Dapat dicetak sebagai Gate Pass setelah diterbitkan.
- Draf tidak dapat dicetak.

### Penerimaan DTY

- Berat berasal dari hasil produksi.
- Jumlah box dapat mengisi bobbin, netto, dan gross secara otomatis.
- Nilai otomatis tetap dapat diubah manual.
- Merge POY asal wajib diisi untuk laporan rendemen.
- Gross dipakai untuk validasi dan toleransi per grade.

## Pengeluaran

### Pengeluaran POY

- Memotong stok aktif, bukan buffer.
- Ditolak bila stok aktif tidak cukup.
- Dapat di-void oleh Admin dengan alasan.

### Surat Jalan DTY

- Memakai lifecycle dokumen resmi sampai `ISSUED`.
- Nomor surat jalan dibuat otomatis dan atomik.
- Dapat dicetak sebagai Surat Jalan.
- Data pengiriman seperti kendaraan dan sopir diisi pada header dokumen.

### Scrap / Reject DTY

- Wajib disertai catatan alasan scrap.
- Memotong stok aktif DTY.
- Dapat di-void oleh Admin dengan alasan.

## Persediaan

### Stok Barang

- Menampilkan posisi per barang, merge, dan grade.
- Memisahkan `stok_aktif` dan `stok_buffer`.
- Total stok = aktif + buffer.
- Pallet hanya indikator visual, bukan nilai akuntansi.
- Stok barang tidak dapat diubah langsung dari tabel.

### Stok Opname

- Hanya Admin.
- Membandingkan stok sistem (aktif + buffer) dengan stok fisik.
- Selisih positif membuat transaksi penyesuaian masuk.
- Selisih negatif membuat transaksi penyesuaian keluar.
- Satu posisi hanya boleh diopname sekali per tanggal.
- Stok fisik tidak boleh negatif.

## Laporan

### Keluar-Masuk

- Membaca ledger `mutasi_stok`.
- Filter periode, kategori, arus, dan merge.
- Total masuk, keluar, dan bersih.
- Ekspor Excel, PDF, dan cetak.

### Kartu Stok

- Filter barang, merge, grade, pos, dan periode.
- Menampilkan saldo awal dan saldo berjalan.
- Ekspor Excel, PDF, dan cetak.

### Rendemen

![Laporan Rendemen](assets/rendemen.png)

- Membandingkan POY keluar produksi dengan DTY masuk produksi.
- Dikelompokkan per merge POY asal.
- Menampilkan rendemen dan waste dalam persen.
- Menyertakan disclaimer bahwa rendemen adalah indikator efisiensi massa,
  bukan konversi teknis.
- Ekspor Excel, PDF, dan cetak.

## Pengguna dan riwayat aktivitas

- **Pengguna** — tambah, ubah, nonaktifkan, dan atur role. Pengguna tidak
  dihapus permanen.
- **Riwayat Aktivitas** — mencatat login, logout, create, update, delete,
  void, dan rilis buffer.

Keduanya hanya dapat diakses Admin.

## Notifikasi

![Notifikasi sidebar](assets/notifikasi.png)

- Notifikasi disimpan di tabel `notifications`.
- Drawer notifikasi tersedia di sidebar.
- Notifikasi contoh dibuat oleh seeder demo pada environment lokal.
- Notifikasi juga dikirim setelah aksi tertentu, misalnya rilis buffer.

## Global search

- Buka dengan **Ctrl+K** atau **⌘K**.
- Mencari master data dan transaksi yang diizinkan oleh policy.
- Hasil pencarian menghormati role dan status data.

## Cetak dan ekspor

- **Cetak Gate Pass** untuk penerimaan item pendukung yang sudah diterbitkan.
- **Cetak Surat Jalan** untuk pengiriman DTY.
- **Excel** untuk laporan keluar-masuk, kartu stok, dan rendemen.
- **PDF** dan **Cetak** memakai tampilan laporan khusus.
- Halaman cetak memakai layout tanpa sidebar agar siap dicetak.

## Login

![Halaman login](assets/login.png)

- URL: `/admin/login`.
- Login memakai email.
- Akun default tersedia setelah seeder atau impor dump.
- Ganti password default setelah login pertama.
