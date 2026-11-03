# Testing

Proyek memakai **PHPUnit 12.5.42**. Suite saat ini berisi **201 test** dengan
**1068 assertions** dan seluruhnya lulus.

## Konfigurasi test

`phpunit.xml` mengatur environment test:

- Database: SQLite in-memory (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`)
- Cache dan session: array
- Queue: sync
- Mail: array
- Bcrypt rounds: 4
- `APP_ENV=testing`

SQLite in-memory membuat test cepat dan tidak menyentuh database
pengembangan atau produksi.

## Struktur test

```text
tests/
├── Concerns/
│   └── MembuatDataUji.php
├── Feature/
│   ├── InboundTest.php
│   ├── OutboundTest.php
│   ├── ServiceStokTest.php
│   ├── AlokasiBufferTest.php
│   ├── StokOpnameTest.php
│   ├── VoidTransaksiTest.php
│   ├── LaporanTest.php
│   ├── LaporanExportTest.php
│   ├── DashboardTest.php
│   ├── DemoTransaksiSeederTest.php
│   ├── PenerimaanDetailMapperTest.php
│   ├── FilamentPanelTest.php
│   ├── FilamentBrandingTest.php
│   ├── FilamentDashboardWidgetsTest.php
│   ├── FilamentMasterDataTest.php
│   ├── FilamentPenerimaanCreateTest.php
│   ├── FilamentPengeluaranTest.php
│   ├── FilamentStokBarangTest.php
│   ├── FilamentStokOpnameTest.php
│   ├── FilamentTransaksiMasukTest.php
│   ├── FilamentGatePassPrintTest.php
│   ├── FilamentLaporanTest.php
│   ├── FilamentActivityLogTest.php
│   ├── FilamentUserTest.php
│   └── ExampleTest.php
├── Unit/
│   └── ExampleTest.php
└── TestCase.php
```

Kelompok cakupan:

| Kelompok | Fokus |
| --- | --- |
| Domain stok | Tambah, kurang, alokasi, rilis, saldo non-negatif |
| Buffer Minggu | Ambang pasokan, kuota total, hari kerja, rilis |
| Penerimaan | Posting, issue, validasi kategori/grade, double-posting |
| Pengeluaran | Stok aktif, scrap, surat jalan, nomor atomik |
| Void | Pembalikan mutasi, alasan wajib, role Admin |
| Opname | Selisih, transaksi penyesuaian, duplikat harian |
| Laporan | Agregasi, rendemen, ekspor Excel/PDF |
| Filament | Akses panel, resource, widget, cetak, pengguna |
| Seeder | Konsistensi data demo |

## Menjalankan test

Seluruh suite:

```bash
php artisan test
```

Hanya file tertentu:

```bash
php artisan test --compact tests/Feature/InboundTest.php
```

Filter nama test:

```bash
php artisan test --compact --filter=test_nama_skenario
```

Atau langsung melalui PHPUnit:

```bash
vendor/bin/phpunit tests/Feature/InboundTest.php
```

## Menambah test

1. Buat feature test:

   ```bash
   php artisan make:test --phpunit NamaFiturTest
   ```

2. Gunakan factory yang tersedia. Saat ini tersedia `UserFactory`.
3. Untuk data domain, gunakan helper di `tests/Concerns/MembuatDataUji.php`
   atau buat data melalui model dan service agar aturan bisnis tetap
   dijalankan.
4. Tempatkan test HTTP/panel di `tests/Feature/`; test logika murni di
   `tests/Unit/`.
5. Uji perilaku dan failure mode penting, bukan detail implementasi.
6. Jalankan test yang terdampak setelah setiap perubahan.

Contoh struktur test:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ContohFiturTest extends TestCase
{
    public function test_perilaku_utama_berjalan(): void
    {
        $this->assertTrue(true);
    }
}
```

## Test dan database

- Jangan mengarahkan test ke MySQL lokal; konfigurasi SQLite in-memory
  sudah disiapkan.
- Jangan memakai `RefreshDatabase` bila tidak diperlukan; ikuti pola test
  yang sudah ada.
- Untuk test yang membutuhkan user login, gunakan factory dan role yang
  sesuai.
- Test panel Filament sebaiknya memeriksa akses role, bukan hanya render
  halaman.

## Pint

Setelah mengubah file PHP:

```bash
vendor/bin/pint --dirty
```

Untuk memeriksa tanpa mengubah file:

```bash
vendor/bin/pint --test
```

## Build frontend

Perubahan tampilan harus diikuti build:

```bash
npm run build
```

Untuk pengembangan gunakan `npm run dev`.

## Checklist sebelum commit

1. `php artisan test` lulus.
2. `vendor/bin/pint --dirty` bersih.
3. `npm run build` berhasil bila ada perubahan aset.
4. Dokumentasi diperbarui bila perilaku berubah.
5. Tidak ada data sensitif atau dump lokal yang ikut ter-stage.
