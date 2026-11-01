<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PRINSIP DASAR: KG = SINGLE SOURCE OF TRUTH
    |--------------------------------------------------------------------------
    |
    | Box / Bobbin / Pallet HANYA satuan kemasan fisik (input & display).
    | Saldo stok & mutasi MUTLAK dihitung dari Net Weight (Kg) hasil
    | timbangan nyata / Surat Jalan / Gate Pass.
    |
    | Berkas ini adalah SATU-SATUNYA sumber konstanta bisnis (PRD §3.3).
    | Tidak boleh ada angka hardcode di view/controller/service.
    |
    */

    'satuan' => [

        /*
        | ---------------- POY (250/48) — TERVERIFIKASI LOGBOOK ----------------
        |
        | Bukti: logbook "DATA POY DIKIRIM DARI IPCI KE PEI"
        |   7 pallet   = 252 bobbin = 4.032 kg -> 36 bobbin/pallet, 16 kg/bobbin
        |   180 bobbin = 2.880 kg              -> 16 kg/bobbin (konsisten)
        */
        'poy_bobbin_kg' => 16.0,        // TERVERIFIKASI, presisi
        'poy_pallet_bobbin' => 36,      // 252 / 7
        'poy_pallet_kg' => 576.0,       // 4.032 / 7 (acuan resmi ambang)

        // Catatan: 16 kg/bobbin hanya VALID untuk POY 250/48 (data yang ada).
        // Denier lain HARUS dikonfirmasi ulang. Saldo POY tetap dari Kg
        // timbangan, bukan hasil kali bobbin.

        /*
        | ---------------- DTY (150/48) ----------------
        |
        | Bukti: coretan standar packing + Surat Jalan 244/DTY/PEI/X/2026
        */
        'dty_bobbin_kg' => 5.5,         // 5.500 gram netto (SJ: 1.980/360 = 5,5)
        'dty_box_bobbin' => 6,          // 1 carton box DTY = 6 bobbin
        'dty_box_netto_kg' => 33.0,     // 5,5 x 6 (SJ: 1.980/60 = 33)
        'dty_box_gross_kg' => 35.74,    // AKTUAL Surat Jalan (2.144,4/60)

        /*
        | --- Toleransi gross per box PER GRADE (konfirmasi PIC 30-10-2026) ---
        |
        | Rentang grade A/B jauh lebih lebar: menerima bobbin sisa/putus.
        | Catatan: AE adalah nilai estimasi lapangan (asumsi terkontrol).
        */
        'dty_gross_box_per_grade' => [
            'AX' => ['min' => 34.74, 'max' => 36.25],
            'AE' => ['min' => 33.50, 'max' => 34.60], // estimasi
            'A' => ['min' => 29.90, 'max' => 36.25],
            'B' => ['min' => 29.90, 'max' => 36.25],
        ],

        // 'dty_box_gross_ref' => 35.64, // coretan "coating" (referensi lama)

    ],

    /*
    |--------------------------------------------------------------------------
    | PALLET — POY vs DTY BERATNYA SANGAT BERBEDA
    |--------------------------------------------------------------------------
    |
    |   POY :  36 bobbin/pallet ->    576 kg/pallet
    |   DTY : ~209 bobbin/pallet -> ~1.149 kg/pallet (turunan SJ 244)
    |
    | Nilai di bawah HANYA untuk threshold visual.
    | JANGAN dipakai menambah/mengurangi stok (lihat A1/A2).
    |
    */
    'pallet' => [
        'poy_kg' => 576.0,      // acuan resmi (logbook 7 pallet = 4.032 kg)
        'dty_kg' => 1149.0,     // turunan SJ 244 — indikatif
    ],

    /*
    |--------------------------------------------------------------------------
    | SHIFT KERJA (TERKONFIRMASI PIC 30-10-2026)
    |--------------------------------------------------------------------------
    |
    | Shift 3 melintasi tengah malam. Perhitungan tanggal bisnis harus
    | memperhatikan hal ini (lihat A10).
    |
    */
    'shift' => [
        1 => ['mulai' => '06:00', 'selesai' => '14:00'],
        2 => ['mulai' => '14:00', 'selesai' => '22:00'],
        3 => ['mulai' => '22:00', 'selesai' => '06:00'],
    ],

    /*
    |--------------------------------------------------------------------------
    | PARAMETER OPERASIONAL
    |--------------------------------------------------------------------------
    |
    | Mesin beroperasi 24/7. POY masuk Senin–Sabtu (Minggu libur kirim).
    | 7 pallet masuk - 6 pallet konsumsi = 1 pallet disisihkan/hari kerja
    | -> akumulasi 6 pallet untuk hari Minggu.
    |
    */
    'operasional' => [
        'konsumsi_pallet_hari' => 6,
        'pasokan_pallet_hari' => 7,
        'buffer_pallet_hari' => 1,
        'buffer_hari' => 6,
    ],

    /*
    |--------------------------------------------------------------------------
    | AMBANG POY — DUA INDIKATOR TERPISAH (PRD §5.2.3)
    |--------------------------------------------------------------------------
    |
    |   A. Kecukupan stok AKTIF -> konsumsi 1 hari kerja
    |   B. Kesiapan stok BUFFER -> target akumulasi Minggu
    |
    | Tidak ada ambang tunggal. Nilai kg diturunkan dari pallet.poy_kg.
    |
    */
    'ambang_poy' => [
        'min_stok_aktif_pallet' => 6,   // 6 x 576 = 3.456 kg
        'target_buffer_pallet' => 6,    // 6 x 576 = 3.456 kg

        // Buffer HANYA untuk merge bertanda 'is_merge_minggu' pada master barang.
        // Pasokan < 7 pallet -> buffer DILEWATI (tidak dipotong proporsional).
        'lewati_buffer_jika_kurang_dari' => 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | AMBANG ITEM PENDUKUNG
    |--------------------------------------------------------------------------
    |
    | Nilai default saat seeding ke barang.stok_minimum.
    | Satuan mengikuti satuan dasar barang (PCS / KG).
    |
    */
    'ambang_pendukung' => [
        'PAPER_TUBE' => 1000,
        'CARTON_BOX' => 200,
        'CONING_OIL' => 200,
    ],

    /*
    |--------------------------------------------------------------------------
    | STATUS DOKUMEN & TRANSAKSI (PRD §7.6)
    |--------------------------------------------------------------------------
    |
    | Satu enum `status` dipakai bersama, tetapi kombinasi yang legal
    | berbeda antara transaksi internal dan dokumen resmi eksternal.
    |
    */
    'status' => [
        'draft' => 'DRAFT',
        'posted' => 'POSTED',
        'issued' => 'ISSUED',
        'void' => 'VOID',
        'cancelled' => 'CANCELLED',
    ],

    /*
    | Transisi status yang diizinkan. Dipakai service layer untuk validasi.
    */
    'status_transisi' => [
        // Transaksi internal gudang
        'POSTED' => ['VOID'],
        // Dokumen resmi eksternal
        'ISSUED' => ['CANCELLED', 'VOID'],
        // Draf dapat diposting (internal) atau di-issue (dokumen resmi)
        'DRAFT' => ['POSTED', 'ISSUED', 'CANCELLED', 'VOID'],
        // Status final
        'VOID' => [],
        'CANCELLED' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | PENOMORAN DOKUMEN
    |--------------------------------------------------------------------------
    |
    | Format Surat Jalan: [No_Urut]/DTY/PEI/[Bulan_Romawi]/[Tahun]
    | contoh: 244/DTY/PEI/X/2026
    |
    */
    'bulan_romawi' => [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
        7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ],

    'format_nomor' => [
        'SURAT_JALAN_DTY' => '{nomor}/DTY/PEI/{bulan_romawi}/{tahun}',
        'GATE_PASS' => '{nomor}/GP/PEI/{bulan_romawi}/{tahun}',
        'TRX_MASUK' => 'IN-{periode}-{nomor}',
        'TRX_KELUAR' => 'OUT-{periode}-{nomor}',
    ],

    /*
    | Periode reset nomor: bulanan (YYYY-MM) atau harian (YYYY-MM-DD).
    */
    'periode_nomor' => [
        'SURAT_JALAN_DTY' => 'bulanan',
        'GATE_PASS' => 'bulanan',
        'TRX_MASUK' => 'bulanan',
        'TRX_KELUAR' => 'bulanan',
    ],

    /*
    |--------------------------------------------------------------------------
    | GRED DEFAULT (untuk seeder)
    |--------------------------------------------------------------------------
    |
    | Grade C SENGAJA tetap ada & aktif. Data riil logbook tidak memuatnya,
    | tetapi PIC mengonfirmasi scrap grade C PERNAH keluar dan dicatat
    | (jawaban no. 5, PRD §3.5). Jangan hapus dari master.
    |
    */
    'grade_default' => [
        'POY' => [
            ['kode' => 'AL', 'keterangan' => 'POY grade AL (lolos)'],
            ['kode' => 'Am', 'keterangan' => 'POY grade Am (minor)'],
        ],
        'DTY' => [
            ['kode' => 'AX', 'keterangan' => 'DTY ekspor / super'],
            ['kode' => 'AE', 'keterangan' => 'DTY grade AE'],
            ['kode' => 'A', 'keterangan' => 'DTY standar 1'],
            ['kode' => 'B', 'keterangan' => 'DTY segregasi / turun grade'],
            ['kode' => 'C', 'keterangan' => 'DTY reject / knitting (scrap)'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | KATEGORI & SATUAN DEFAULT (untuk seeder)
    |--------------------------------------------------------------------------
    */
    'kategori_default' => [
        ['kode' => 'POY', 'nama' => 'POY (Bahan Baku Utama)'],
        ['kode' => 'DTY', 'nama' => 'DTY (Produk Jadi)'],
        ['kode' => 'PENDUKUNG', 'nama' => 'Item Pendukung (Packaging & Consumables)'],
    ],

    'satuan_default' => [
        ['kode' => 'KG', 'nama' => 'Kilogram'],
        ['kode' => 'BOX', 'nama' => 'Box / Carton'],
        ['kode' => 'PALLET', 'nama' => 'Pallet'],
        ['kode' => 'PCS', 'nama' => 'Pieces'],
        ['kode' => 'ROLL', 'nama' => 'Roll'],
        ['kode' => 'BOBBIN', 'nama' => 'Bobbin / Gulungan'],
    ],

    /*
    |--------------------------------------------------------------------------
    | RENDERMEN — DISCLAIMER OTOMATIS (PRD §6.3)
    |--------------------------------------------------------------------------
    |
    | WIP diabaikan (A4) dan denier POY != DTY (A5), sehingga rendemen
    | adalah indikator efisiensi massa, bukan konversi teknis.
    |
    */
    'rendemen' => [
        'disclaimer' => 'Rendemen dihitung sebagai indikator efisiensi massa, bukan konversi teknis. '
            .'WIP (work-in-process) diabaikan sehingga hasil dapat sedikit menyimpang dari 100%. '
            .'Denier POY (mis. 250/48) berbeda dari DTY (mis. 150/48), sehingga perbandingan kg '
            .'tidak apple-to-apple. Grade C/B berasal dari POY bermasalah (denier/filament tidak cocok) '
            .'— bukan inefisiensi mesin.',
    ],

];
