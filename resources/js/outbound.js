/*
|--------------------------------------------------------------------------
| Form Pengeluaran (Outbound F3) — Baris Detail Dinamis
|--------------------------------------------------------------------------
| JavaScript vanilla, tanpa library tambahan.
|
| Cerminan inbound.js tanpa auto-fill konversi: tambah/hapus baris dari
| <template id="wms-baris-template">, reindex kontigu agar validasi
| Laravel `detail.*` bekerja, plus anti double-submit.
*/
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('[data-wms-outbound-form]');

        if (!form) {
            return;
        }

        const tbody = form.querySelector('[data-wms-detail-body]');
        const template = document.getElementById('wms-baris-template');
        const tombolTambah = form.querySelector('[data-wms-tambah-baris]');

        if (!tbody || !template) {
            return;
        }

        const reindex = () => {
            Array.from(tbody.querySelectorAll('[data-wms-baris]')).forEach((baris, i) => {
                baris.querySelectorAll('[name^="detail["]').forEach((input) => {
                    input.name = input.name.replace(/^detail\[\d+]/, `detail[${i}]`);
                });

                baris.querySelectorAll('[data-wms-hapus-baris]').forEach((btn) => {
                    btn.disabled = tbody.querySelectorAll('[data-wms-baris]').length <= 1;
                });

                const nomor = baris.querySelector('[data-wms-nomor-baris]');

                if (nomor) {
                    nomor.textContent = i + 1;
                }
            });
        };

        const pasangBaris = (baris) => {
            baris.querySelector('[data-wms-hapus-baris]')?.addEventListener('click', () => {
                if (tbody.querySelectorAll('[data-wms-baris]').length <= 1) {
                    return;
                }

                baris.remove();
                reindex();
            });
        };

        const tambahBaris = () => {
            const fragmen = template.content.cloneNode(true);

            tbody.appendChild(fragmen);
            const barisBaru = tbody.querySelector('[data-wms-baris]:last-of-type');

            pasangBaris(barisBaru);
            reindex();

            return barisBaru;
        };

        Array.from(tbody.querySelectorAll('[data-wms-baris]')).forEach(pasangBaris);
        reindex();

        tombolTambah?.addEventListener('click', (e) => {
            e.preventDefault();
            const baris = tambahBaris();
            baris?.querySelector('select, input')?.focus();
        });

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
