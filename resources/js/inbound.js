/*
|--------------------------------------------------------------------------
| Form Penerimaan (Inbound F2) — Baris Detail Dinamis
|--------------------------------------------------------------------------
| JavaScript vanilla, tanpa library tambahan (tanpa jQuery/Alpine).
|
| Kebutuhan:
|  - Tambah/hapus baris detail tanpa reload.
|  - Baris di-clone dari <template id="wms-baris-template"> sehingga SEMUA
|    atribut `name="detail[i][...]"` dan `@error`-style markup tetap sama
|    dengan yang dirender Blade (satu sumber kebenaran = Blade).
|  - Indeks di-reindex setelah clone/hapus agar tidak ada "lubang" indeks
|    (validasi Laravel `detail.*` bekerja pada array kontigu).
|  - Data master (barang/grade/satuan/konversi) dibaca dari
|    <script type="application/json" id="wms-inbound-data">.
|  - Angka konversi (box->bobbin/netto/gross, toleransi per grade) SELALU
|    dari config via payload — tidak ada angka bisnis hardcode di sini.
|  - submit dinonaktifkan sementara (anti double submit) memakai
|    data-submitting agar form tidak terkirim dua kali.
*/
(function () {
    'use strict';

    /** Payload JSON aman: Blade meng-escape '<' menjadi '\u003C' bila memakai @json. */
    const bacaPayload = () => {
        const el = document.getElementById('wms-inbound-data');

        if (!el) {
            return null;
        }

        try {
            return JSON.parse(el.textContent || '{}');
        } catch (e) {
            console.error('Payload form penerimaan tidak valid', e);
            return null;
        }
    };

    const formatAngka = (nilai, desimal = 2) =>
        Number(nilai || 0).toLocaleString('id-ID', {
            minimumFractionDigits: desimal,
            maximumFractionDigits: desimal,
        });

    /**
     * Isi <select> dari daftar opsi.
     */
    const isiSelect = (select, opsi, placeholder) => {
        if (!select) {
            return;
        }

        const nilaiLama = select.value;
        select.innerHTML = '';

        if (placeholder) {
            const kosong = document.createElement('option');
            kosong.value = '';
            kosong.textContent = placeholder;
            select.appendChild(kosong);
        }

        opsi.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.label;
            select.appendChild(option);
        });

        if (nilaiLama) {
            select.value = nilaiLama;
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('[data-wms-inbound-form]');

        if (!form) {
            return;
        }

        const payload = bacaPayload() || {};
        const tbody = form.querySelector('[data-wms-detail-body]');
        const template = document.getElementById('wms-baris-template');
        const tombolTambah = form.querySelector('[data-wms-tambah-baris]');

        if (!tbody || !template) {
            return;
        }

        const jenis = form.getAttribute('data-wms-inbound-form'); // poy | pendukung | dty
        const konversi = payload.konversi || {};

        /*
        |------------------------------------------------------------------
        | Indeks ulang: pastikan `detail[i][field]` kontigu 0..n-1
        |------------------------------------------------------------------
        */
        const reindex = () => {
            Array.from(tbody.querySelectorAll('[data-wms-baris]')).forEach((baris, i) => {
                baris.querySelectorAll('[name^="detail["]').forEach((input) => {
                    input.name = input.name.replace(/^detail\[\d+]/, `detail[${i}]`);
                });

                baris.querySelectorAll('[data-wms-hapus-baris]').forEach((btn) => {
                    // Baris pertama tidak boleh dihapus (minimal 1 baris).
                    btn.disabled = tbody.querySelectorAll('[data-wms-baris]').length <= 1;
                });

                const nomor = baris.querySelector('[data-wms-nomor-baris]');

                if (nomor) {
                    nomor.textContent = i + 1;
                }
            });
        };

        /*
        |------------------------------------------------------------------
        | Konteks per baris: grade difilter dari barang terpilih?
        |------------------------------------------------------------------
        | Grade pada payload sudah difilter per kategori barang, sehingga
        | cukup diisi sekali per baris. Filter tambahan tidak diperlukan.
        */
        const lengkapiBaris = (baris) => {
            const selectGrade = baris.querySelector('[name$="[grade_id]"]');

            if (selectGrade && selectGrade.options.length === 0 && (payload.grade || []).length) {
                isiSelect(
                    selectGrade,
                    payload.grade.map((g) => ({ id: g.id, label: g.kode })),
                    '— Pilih grade —'
                );
            }

            const selectSatuan = baris.querySelector('[name$="[satuan_kemasan_id]"]');

            if (selectSatuan && selectSatuan.options.length === 0 && (payload.satuan || []).length) {
                isiSelect(
                    selectSatuan,
                    payload.satuan.map((s) => ({ id: s.id, label: s.kode })),
                    '— Satuan —'
                );
            }
        };

        /*
        |------------------------------------------------------------------
        | DTY: auto-fill box -> bobbin / netto / gross + peringatan grade
        |------------------------------------------------------------------
        */
        const hitungDty = (baris) => {
            if (jenis !== 'dty') {
                return;
            }

            const box = parseFloat(baris.querySelector('[name$="[jumlah_box]"]')?.value || '0');
            const lapangan = baris.querySelector('[data-wms-gross-info]');

            if (!(box > 0)) {
                if (lapangan) {
                    lapangan.textContent = '';
                    lapangan.className = 'form-text';
                }
                return;
            }

            const bobbin = Math.round(box * Number(konversi.boxBobbin || 0));
            const netto = box * Number(konversi.boxNettoKg || 0);
            const gross = box * Number(konversi.boxGrossKg || 0);

            const inputBobbin = baris.querySelector('[name$="[jumlah_bobbin]"]');
            const inputNetto = baris.querySelector('[name$="[berat_kg]"]');
            const inputGross = baris.querySelector('[name$="[berat_gross_kg]"]');

            // Hanya isi bila operator belum mengisi (override manual dihormati).
            if (inputBobbin && !inputBobbin.dataset.wmsManual) {
                inputBobbin.value = bobbin;
            }
            if (inputNetto && !inputNetto.dataset.wmsManual) {
                inputNetto.value = netto.toFixed(2);
            }
            if (inputGross && !inputGross.dataset.wmsManual) {
                inputGross.value = gross.toFixed(2);
            }

            periksaGross(baris);
        };

        const periksaGross = (baris) => {
            const info = baris.querySelector('[data-wms-gross-info]');

            if (!info) {
                return;
            }

            const box = parseFloat(baris.querySelector('[name$="[jumlah_box]"]')?.value || '0');
            const gross = parseFloat(baris.querySelector('[name$="[berat_gross_kg]"]')?.value || '0');
            const gradeId = baris.querySelector('[name$="[grade_id]"]')?.value;

            info.textContent = '';
            info.className = 'form-text';

            if (!(box > 0) || !(gross > 0) || !gradeId) {
                return;
            }

            const grade = (payload.grade || []).find((g) => String(g.id) === String(gradeId));
            const rentang = grade ? konversi.toleransiGrade?.[grade.kode] : null;

            // Grade tanpa entri config (mis. C / scrap) -> validasi dilewati.
            if (!rentang) {
                info.textContent = `Gross/box ${formatAngka(gross / box)} kg — grade ${grade?.kode ?? '—'} tanpa toleransi config (dilewati).`;
                return;
            }

            const perBox = gross / box;
            const luar = perBox < rentang.min || perBox > rentang.max;

            info.textContent =
                `Gross/box ${formatAngka(perBox)} kg — toleransi grade ${grade.kode} ` +
                `${formatAngka(rentang.min)}–${formatAngka(rentang.max)} kg.` +
                (luar ? ' Di luar toleransi (boleh disimpan, mohon periksa bobbin sisa/putus).' : ' Dalam toleransi.');
            info.className = luar ? 'form-text text-warning-emphasis' : 'form-text text-success';
        };

        /*
        |------------------------------------------------------------------
        | Pasang event pada satu baris
        |------------------------------------------------------------------
        */
        const pasangBaris = (baris) => {
            lengkapiBaris(baris);

            baris.querySelector('[data-wms-hapus-baris]')?.addEventListener('click', () => {
                if (tbody.querySelectorAll('[data-wms-baris]').length <= 1) {
                    return;
                }

                baris.remove();
                reindex();
            });

            baris.querySelectorAll('input, select').forEach((input) => {
                // Tandai override manual agar auto-fill tidak menimpa isian operator.
                if (['NUMBER', 'TEXT'].includes(input.tagName) && !input.dataset.wmsAuto) {
                    input.addEventListener('input', () => {
                        input.dataset.wmsManual = '1';
                        if (jenis === 'dty') {
                            periksaGross(baris);
                        }
                    });
                }
            });

            if (jenis === 'dty') {
                baris.querySelector('[name$="[jumlah_box]"]')?.addEventListener('input', () => hitungDty(baris));
                baris.querySelector('[name$="[grade_id]"]')?.addEventListener('change', () => periksaGross(baris));
            }
        };

        /*
        |------------------------------------------------------------------
        | Tambah baris dari template
        |------------------------------------------------------------------
        */
        const tambahBaris = (nilaiAwal) => {
            const fragmen = template.content.cloneNode(true);
            const baris = fragmen.querySelector('[data-wms-baris]');

            if (nilaiAwal) {
                Object.entries(nilaiAwal).forEach(([kunci, nilai]) => {
                    const input = baris.querySelector(`[name$="[${kunci}]"]`);

                    if (input && nilai !== null && nilai !== undefined) {
                        input.value = nilai;
                    }
                });
            }

            tbody.appendChild(fragmen);
            const barisBaru = tbody.querySelector('[data-wms-baris]:last-of-type');

            pasangBaris(barisBaru);
            reindex();

            return barisBaru;
        };

        // Baris yang sudah dirender server (old() / data edit) tinggal dipasangi event.
        Array.from(tbody.querySelectorAll('[data-wms-baris]')).forEach(pasangBaris);
        reindex();

        // Baris kosong terakhir ditambahkan otomatis bila form edit dibuka.
        tombolTambah?.addEventListener('click', (e) => {
            e.preventDefault();
            const baris = tambahBaris();
            baris?.querySelector('select, input')?.focus();
        });

        /*
        |------------------------------------------------------------------
        | Anti double-submit
        |------------------------------------------------------------------
        */
        form.addEventListener('submit', () => {
            if (form.dataset.submitting === '1') {
                return;
            }

            form.dataset.submitting = '1';
            form.querySelectorAll('button[type="submit"]').forEach((btn) => {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan…';
            });
        });
    });
})();
