<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\KategoriBarang;
use App\Models\StokBarang;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

/**
 * Rilis buffer Minggu ke stok aktif (PRD §5.2.1, F4).
 *
 * Hanya untuk barang POY bertanda is_merge_minggu. Setiap baris stok
 * yang buffer-nya > 0 dipindahkan seluruhnya BUFFER -> AKTIF lewat
 * StokService::rilisBuffer() (dua baris mutasi RILIS_BUFFER per baris).
 * Idempoten: buffer 0 -> pulang tanpa error dan tanpa log.
 */
class RilisBufferService
{
    private const MODUL = 'RILIS_BUFFER';

    public function __construct(private readonly StokService $stok) {}

    /**
     * @return array{dirilis_kg: float, baris: int}
     */
    public function rilis(DateTimeInterface $tanggal, ?int $userId = null): array
    {
        $tgl = $tanggal instanceof Carbon
            ? $tanggal->copy()
            : Carbon::parse($tanggal->format('Y-m-d H:i:s'), (string) config('app.timezone', 'Asia/Jakarta'));
        $ref = sprintf('RILIS-BUFFER-%s', $tgl->format('Y-m-d'));

        return DB::transaction(function () use ($tgl, $ref, $userId): array {
            $daftar = StokBarang::query()
                ->hanyaMergeMinggu()
                ->whereHas('barang', fn ($q) => $q->whereHas(
                    'kategori',
                    fn ($k) => $k->where('kode', KategoriBarang::POY)
                ))
                ->where('stok_buffer', '>', 0)
                ->with('barang')
                ->lockForUpdate()
                ->get();

            $dirilis = 0.0;
            $baris = 0;

            foreach ($daftar as $stok) {
                $qty = round((float) $stok->stok_buffer, 2);

                if ($qty <= 0) {
                    continue;
                }

                $this->stok->rilisBuffer(
                    $stok->barang, $stok->merge, $stok->grade_id, $qty, $ref, $userId, $tgl
                );

                $dirilis = round($dirilis + $qty, 2);
                $baris++;
            }

            if ($baris > 0) {
                ActivityLog::catat(
                    'RELEASE_BUFFER',
                    self::MODUL,
                    null,
                    sprintf(
                        'Rilis buffer Minggu %s kg (%d baris) ke stok aktif (%s).',
                        number_format($dirilis, 2, ',', '.'),
                        $baris,
                        $ref
                    ),
                    ['user_id' => $userId]
                );
            }

            return ['dirilis_kg' => $dirilis, 'baris' => $baris];
        });
    }

    /**
     * Rilis otomatis hanya pada hari Minggu (N=7, zona aplikasi).
     */
    public function bolehRilisOtomatis(Carbon $saat): bool
    {
        return (int) $saat->copy()
            ->setTimezone((string) config('app.timezone', 'Asia/Jakarta'))
            ->format('N') === 7;
    }
}
