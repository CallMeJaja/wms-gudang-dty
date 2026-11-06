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
| Data Induk | Satuan | `/admin/satuans` | KG, BOX, PALLET, PCS, BOBBIN |
| Data Induk | Konversi | `/admin/konversi-satuans` | Faktor konversi satuan |
| Penerimaan | POY | `/admin/penerimaan-poy` | Penerimaan bahan baku POY |
| Penerimaan | Gate Pass | `/admin/penerimaan-pendukung` | Item pendukung dengan dokumen resmi |
| Produksi | POY ke Produksi | `/admin/pengeluaran-poy` | Bahan baku POY untuk lantai produksi |
| Produksi | DTY dari Produksi | `/admin/penerimaan-dty` | Penerimaan produk jadi DTY |
| Pengeluaran | DTY (Surat Jalan) | `/admin/surat-jalan-dty` | Pengiriman DTY |
| Persediaan | Stok Barang | `/admin/stok-barangs` | Posisi stok aktif dan buffer |
| Persediaan | Waste / Scrap | `/admin/waste-logs` | Log waste/reject kg tanpa mutasi stok |
| Persediaan | Stok Opname | `/admin/stok-opnames` | Penyesuaian hasil hitung fisik |
| Laporan | Keluar-Masuk | `/admin/laporan/keluar-masuk` | Arus mutasi per periode |
| Laporan | Kartu Stok | `/admin/laporan/kartu-stok` | Saldo berjalan per posisi |
| Laporan | Rendemen | `/admin/laporan/rendemen` | POY masuk vs DTY keluar |
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
| Membuat waste/scrap log | Ya | Ya | Tidak |
| Mengubah/menghapus waste/scrap log | Ya | Tidak | Tidak |
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
- Nomor Gate Pass dan Surat Jalan dibuat otomatis, unik, dan read-only di form.

## Beranda

Beranda menampilkan:

- **Stok aktif POY** dengan target 6 pallet aktif.
- **Buffer Minggu** dengan target 1 pallet (total 6 aktif + 1 buffer),
  status siap/belum siap/terlambat, dan aksi rilis pada hari Minggu.
- **Draf menunggu posting** untuk penerimaan dan pengeluaran.
- **Grafik 30 hari** yang hanya menghitung mutasi `MASUK` dan `KELUAR`.
- **Perlu perhatian** menggunakan nama barang untuk stok di bawah minimum,
  stok aktif kritis, dan buffer terlambat.

Aksi **Rilis Buffer Minggu** tersedia bagi Admin dan Operator ketika hari
Minggu dan masih ada buffer.

## Data Induk

- **Barang** — kode, nama, kategori, denier, filament, satuan dasar,
  stok minimum, dan penanda merge Minggu. POY/DTY memakai kode internal;
  7 item pendukung memakai kode material IPCI numerik.
- **Mitra** — supplier, customer, gabungan, atau internal.
- **Grade** — unik per kategori, termasuk grade DTY `WE`.
- **Kategori** — POY, DTY, dan item pendukung.
- **Satuan** — KG, BOX, PALLET, PCS, BOBBIN (tanpa ROLL).
- **Konversi** — faktor kemasan ke KG/satuan lain, termasuk konversi POY
  (`PALLET → BOX = 1`, `BOX → BOBBIN = 36`, `BOBBIN → KG = 16`) dan DTY
  `PALLET → BOX = 30`.

Perubahan master data hanya dilakukan Admin. Operator dan Manajer dapat
melihat data.

## Penerimaan

### Penerimaan POY

![Penerimaan POY](assets/penerimaan-poy.png)

- Box/Pallet dan Bobbin saling mengisi otomatis: 1 box/pallet = 36 bobbin
  = 576 kg; 1 bobbin = 16 kg.
- Berat KG tetap dapat disesuaikan dengan hasil timbangan dan menjadi dasar saldo.
- Ref. Dokumen bersifat opsional; kolom tabelnya tersembunyi secara default.
- Satu baris mewakili kombinasi barang, merge, dan grade.
- Setelah posting, stok aktif bertambah dan buffer dapat dialokasikan
  sesuai aturan Minggu.

### Gate Pass Pendukung

- Untuk paper tube, carton box, oli, pallet, dan item pendukung lain.
- Identitas barang memakai `kode_barang` numerik IPCI.
- Nomor Gate Pass otomatis, unik, dan read-only; tidak ada field Ref. Dokumen.
- Memakai lifecycle dokumen resmi sampai `ISSUED`.
- Batch hanya metadata informasi (mis. warna paper tube / kode oli); stok item pendukung selalu memakai identitas `merge = -` dan grade `STD`.
- Catatan baris disimpan terpisah dari batch.
- Dapat dicetak sebagai Gate Pass setelah diterbitkan; kolom Batch memakai metadata batch.
- Draf, void, dan cancelled tidak dapat dicetak.

## Produksi

### DTY dari Produksi

- Berat berasal dari hasil produksi; pengirim default **Area Produksi**.
- Box mengisi bobbin, netto, dan gross otomatis; semua nilai tetap dapat
  disesuaikan dengan timbangan.
- Batch/merge opsional; kosong disimpan sebagai `-`.
- Merge POY asal wajib diisi untuk identitas produksi.
- Gross dipakai untuk validasi dan toleransi per grade.

### POY ke Produksi

- Memotong stok aktif, bukan buffer.
- Box/Pallet dan Bobbin saling mengisi otomatis; netto dapat disesuaikan
  dengan timbangan.
- Ditolak bila stok aktif tidak cukup.
- Dapat di-void oleh Admin dengan alasan.

## Pengeluaran

### DTY (Surat Jalan)

- Memakai lifecycle dokumen resmi sampai `ISSUED`.
- Nomor Surat Jalan otomatis, unik, dan read-only di form.
- Detail hanya memakai Barang, Merge, Grade, Bobbin, Netto, dan Gross;
  tanpa Kemasan/Keterangan.
- Bobbin mengisi netto dan gross otomatis; keduanya tetap dapat disesuaikan.
- Dapat dicetak sebagai Surat Jalan hanya saat `ISSUED`.
- Data pengiriman seperti kendaraan dan sopir diisi pada header dokumen.

## Persediaan

### Stok Barang

- Menampilkan posisi per barang, merge, dan grade.
- Memisahkan `stok_aktif` dan `stok_buffer`.
- Total stok = aktif + buffer.
- Pallet hanya indikator visual, bukan nilai akuntansi.
- Stok barang tidak dapat diubah langsung dari tabel.

### Waste / Scrap

- Mencatat waste/reject yarn dalam kg saja: tanpa grade, merge, kode barang, atau kemasan.
- Field: tanggal, jenis (Waste/Reject/Scrap Packaging), tujuan (RCL/EYD/Pengepul/Lainnya), berat kg, dan keterangan opsional.
- Tidak mengurangi stok DTY dan tidak membuat mutasi stok.
- Admin dan Operator dapat membuat catatan; Admin dapat mengubah/menghapus; Manajer read-only.
- Resource lama `Pengeluaran Scrap` tetap ada sebagai legacy tetapi disembunyikan dari navigasi.

### Stok Opname

- Hanya Admin.
- Hanya boleh pada hari terakhir bulan.
- Membandingkan stok sistem (aktif + buffer) dengan stok fisik.
- POY/DTY memakai merge dan grade; item pendukung otomatis memakai `-` dan grade `STD`.
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

- Membandingkan POY masuk dari IPCI dengan DTY keluar lewat Surat Jalan.
- Default periode: 30 hari terakhir termasuk hari ini.
- Menampilkan POY masuk, DTY keluar, rendemen, waste/selisih kg dan %.
- Target bulanan 100 ton beserta pencapaian dan selisih target.
- Memberi peringatan bila DTY keluar lebih besar dari POY masuk (surplus/data tidak valid).
- Menyertakan disclaimer bahwa waste mencakup area, WIP, dan kehilangan proses.
- Ekspor Excel, PDF, dan cetak.

## Pengguna dan riwayat aktivitas

- **Pengguna** — tambah, ubah, nonaktifkan, dan atur role. Pengguna tidak
  dihapus permanen.
- **Riwayat Aktivitas** — mencatat login, logout, create, update, delete,
  void, dan rilis buffer.

Keduanya hanya dapat diakses Admin.

## Notifikasi

![Notifikasi sidebar](assets/notifikasi.png)

- Notifikasi disimpan di tabel `notifications` dan tersedia di drawer sidebar.
- Notifikasi dikirim langsung (sinkron) sehingga tetap tampil tanpa worker
  queue.
- Notifikasi stok menipis dikirim otomatis setelah mutasi stok (penerimaan,
  pengeluaran, alokasi buffer, dan rilis buffer) serta oleh pemeriksaan
  terjadwal setiap jam.
- Pemeriksaan memakai total `stok_aktif + stok_buffer` untuk barang aktif yang
  memiliki `stok_minimum`. Stok aktif POY juga diperiksa terhadap ambang
  6 pallet.
- Penerima hanya pengguna aktif dengan role Admin dan Manajer.
- Notifikasi memakai nama barang pada judul, dikirim ke Admin/Manajer aktif,
  dengan dedup 24 jam per penerima dan pemeriksaan jam-an.
- Seeder demo `NotifikasiDemoSeeder` menjalankan pemeriksaan nyata ini, bukan
  membuat notifikasi contoh palsu.

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
