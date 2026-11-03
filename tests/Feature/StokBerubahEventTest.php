<?php

namespace Tests\Feature;

use App\Events\StokBerubah;
use App\Services\StokService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\MembuatDataUji;
use Tests\TestCase;

class StokBerubahEventTest extends TestCase
{
    use DatabaseMigrations;
    use MembuatDataUji;

    #[Test]
    public function tambah_stok_mendispatch_event_stok_berubah_dengan_barang_id_benar(): void
    {
        $this->siapkanMasterDasar();
        Event::fake([StokBerubah::class]);
        $barang = $this->barangDty();

        $this->app->make(StokService::class)->tambahStok(
            $barang,
            'M-001',
            $this->gradeDtyA->id,
            10.0,
        );

        Event::assertDispatched(
            StokBerubah::class,
            fn (StokBerubah $event): bool => $event->barangId === $barang->id,
        );
    }
}
