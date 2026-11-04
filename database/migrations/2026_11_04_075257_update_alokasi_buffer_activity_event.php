<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ubahEnumEvent(true);

        DB::table('activity_logs')
            ->where('modul', 'ALOKASI_BUFFER')
            ->where('event', 'RELEASE_BUFFER')
            ->update(['event' => 'ALOKASI_BUFFER']);
    }

    public function down(): void
    {
        DB::table('activity_logs')
            ->where('modul', 'ALOKASI_BUFFER')
            ->where('event', 'ALOKASI_BUFFER')
            ->update(['event' => 'RELEASE_BUFFER']);

        $this->ubahEnumEvent(false);
    }

    private function ubahEnumEvent(bool $tambah): void
    {
        $nilai = ['LOGIN', 'LOGOUT', 'CREATE', 'UPDATE', 'DELETE', 'VOID', 'RELEASE_BUFFER'];

        if ($tambah) {
            array_splice($nilai, 6, 0, 'ALOKASI_BUFFER');
        }

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $enum = implode(',', array_map(fn (string $item): string => "'{$item}'", $nilai));

            DB::statement("ALTER TABLE `activity_logs` MODIFY `event` ENUM({$enum}) NOT NULL");

            return;
        }

        if ($driver === 'sqlite') {
            Schema::table('activity_logs', function (Blueprint $table) use ($nilai): void {
                $table->enum('event', $nilai)->change();
            });
        }
    }
};
