<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WasteLog;
use Illuminate\Database\Seeder;

class WasteLogSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('role', 'admin')->value('id');

        $daftar = [
            ['hari' => 5, 'jenis' => WasteLog::JENIS_WASTE, 'tujuan' => 'RCL', 'berat' => 145.50, 'keterangan' => 'Waste benang dari area produksi.'],
            ['hari' => 3, 'jenis' => WasteLog::JENIS_REJECT, 'tujuan' => 'EYD', 'berat' => 82.25, 'keterangan' => 'Reject benang, dikirim ke EYD.'],
            ['hari' => 1, 'jenis' => WasteLog::JENIS_SCRAP_PACKAGING, 'tujuan' => 'Pengepul', 'berat' => 36.80, 'keterangan' => 'Scrap kemasan carton.'],
            ['hari' => 0, 'jenis' => WasteLog::JENIS_REJECT, 'tujuan' => 'RCL', 'berat' => 55.00, 'keterangan' => 'Reject hasil sortir akhir.'],
        ];

        foreach ($daftar as $item) {
            $tanggal = now()->subDays($item['hari'])->toDateString();

            $sudah = WasteLog::query()
                ->whereDate('tanggal', $tanggal)
                ->where('jenis', $item['jenis'])
                ->where('tujuan', $item['tujuan'])
                ->exists();

            if ($sudah) {
                continue;
            }

            WasteLog::create([
                'tanggal' => $tanggal,
                'jenis' => $item['jenis'],
                'tujuan' => $item['tujuan'],
                'berat_kg' => $item['berat'],
                'keterangan' => $item['keterangan'],
                'created_by' => $adminId,
            ]);
        }
    }
}
