import * as bootstrap from 'bootstrap';
import Chart from 'chart.js/auto';

// Modul Transaksi Masuk (F2): baris detail dinamis form penerimaan.
import './inbound.js';

// Modul Transaksi Keluar (F3): baris detail dinamis form pengeluaran.
import './outbound.js';
import './sidebar.js';

/*
|--------------------------------------------------------------------------
| Bootstrap JS
|--------------------------------------------------------------------------
| Diekspos ke window agar bisa dipakai inline pada Blade (tooltip,
| modal, dropdown) tanpa perlu import ulang per halaman.
*/
window.bootstrap = bootstrap;

/*
|--------------------------------------------------------------------------
| Chart.js
|--------------------------------------------------------------------------
| Dipakai modul Dashboard (F4) untuk grafik stok harian.
| window.WmsChart dipakai sebagai helper pembuat grafik.
*/
window.Chart = Chart;

window.WmsChart = {
    /**
     * Buat grafik garis stok harian.
     *
     * @param {HTMLCanvasElement|string} target
     * @param {{labels: string[], datasets: object[]}} data
     * @param {object} options
     * @returns {Chart}
     */
    line(target, data, options = {}) {
        const el = typeof target === 'string' ? document.querySelector(target) : target;

        if (!el) {
            return null;
        }

        return new Chart(el, {
            type: 'line',
            data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { padding: 10, cornerRadius: 6 },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
                ...options,
            },
        });
    },
};

/*
|--------------------------------------------------------------------------
| Perilaku global
|--------------------------------------------------------------------------
*/

// Aktifkan semua tooltip Bootstrap secara otomatis.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        bootstrap.Tooltip.getOrCreateInstance(el);
    });

    // Konfirmasi sebelum aksi destruktif (void transaksi, hapus data).
    document.querySelectorAll('[data-wms-confirm]').forEach((el) => {
        el.addEventListener('click', (event) => {
            const message = el.getAttribute('data-wms-confirm') || 'Yakin melanjutkan aksi ini?';

            if (!window.confirm(message)) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        });
    });
});
