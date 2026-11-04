<?php

namespace Tests\Feature;

use App\Services\ShiftService;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShiftServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function current_shift_mengikuti_batas_config(): void
    {
        $service = $this->app->make(ShiftService::class);

        foreach ([
            '05:59' => 3,
            '06:00' => 1,
            '13:59' => 1,
            '14:00' => 2,
            '21:59' => 2,
            '22:00' => 3,
            '23:59' => 3,
            '00:00' => 3,
        ] as $jam => $shift) {
            Carbon::setTestNow(Carbon::parse("2026-11-04 {$jam}:00"));

            $this->assertSame($shift, $service->current(), "Shift untuk {$jam} salah.");
        }
    }

    #[Test]
    public function label_dan_options_memakai_format_yang_sama(): void
    {
        $service = $this->app->make(ShiftService::class);

        $this->assertSame('Shift 1 (06:00–14:00)', $service->label(1));
        $this->assertSame(
            [1 => '1 (06:00–14:00)', 2 => '2 (14:00–22:00)', 3 => '3 (22:00–06:00)'],
            $service->options(),
        );
    }
}
