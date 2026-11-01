<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

/**
 * Uji endpoint export laporan milik panel Filament (`admin.laporan.*`).
 *
 * DB: SQLite :memory: (phpunit.xml) — MySQL dev TIDAK tersentuh.
 */
class LaporanExportTest extends TestCase
{
    use MembuatDataUji;
    use RefreshDatabase;

    private User $admin;

    private Barang $poy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanMasterDasar();
        $this->kunciHariKerja();

        $this->admin = User::factory()->admin()->create();
        $this->poy = $this->barangPoy();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function admin_bisa_mengunduh_semua_export_excel(): void
    {
        $filter = $this->filter();

        foreach ([
            'admin.laporan.keluar-masuk.excel' => 'laporan-keluar-masuk.xlsx',
            'admin.laporan.kartu-stok.excel' => 'kartu-stok.xlsx',
            'admin.laporan.rendemen.excel' => 'rendemen-poy-dty.xlsx',
        ] as $route => $namaBerkas) {
            $respon = $this->actingAs($this->admin)->get(route($route, $filter));

            $respon->assertOk();
            $this->assertStringContainsString(
                'spreadsheetml',
                (string) $respon->headers->get('Content-Type'),
                "Export {$route} harus berformat xlsx.",
            );
            $this->assertStringContainsString(
                $namaBerkas,
                (string) $respon->headers->get('Content-Disposition'),
                "Export {$route} harus memakai nama berkas {$namaBerkas}.",
            );
            $this->assertInstanceOf(BinaryFileResponse::class, $respon->baseResponse);
            $this->assertGreaterThan(0, $respon->baseResponse->getFile()->getSize());
        }
    }

    #[Test]
    public function admin_bisa_mengunduh_semua_export_pdf(): void
    {
        $filter = $this->filter();

        foreach ([
            'admin.laporan.keluar-masuk.pdf' => 'laporan-keluar-masuk.pdf',
            'admin.laporan.kartu-stok.pdf' => 'kartu-stok.pdf',
            'admin.laporan.rendemen.pdf' => 'rendemen-poy-dty.pdf',
        ] as $route => $namaBerkas) {
            $respon = $this->actingAs($this->admin)->get(route($route, $filter));

            $respon->assertOk();
            $this->assertStringContainsString(
                'pdf',
                strtolower((string) $respon->headers->get('Content-Type')),
                "Export {$route} harus berformat pdf.",
            );
            $this->assertStringContainsString(
                $namaBerkas,
                (string) $respon->headers->get('Content-Disposition'),
                "Export {$route} harus memakai nama berkas {$namaBerkas}.",
            );
            $this->assertGreaterThan(0, strlen((string) $respon->getContent()));
        }
    }

    #[Test]
    public function tamu_diarahkan_ke_login_untuk_semua_export(): void
    {
        $filter = $this->filter();

        $rute = [
            'admin.laporan.keluar-masuk.excel',
            'admin.laporan.keluar-masuk.pdf',
            'admin.laporan.kartu-stok.excel',
            'admin.laporan.kartu-stok.pdf',
            'admin.laporan.rendemen.excel',
            'admin.laporan.rendemen.pdf',
        ];

        foreach ($rute as $route) {
            $this->get(route($route, $filter))->assertRedirect('/admin/login');
        }
    }

    /**
     * @return array<string, int|string>
     */
    private function filter(): array
    {
        return [
            'dari' => '2026-10-01',
            'sampai' => '2026-10-31',
            'barang_id' => $this->poy->id,
            'merge' => 'M-EXPORT-1',
        ];
    }
}
