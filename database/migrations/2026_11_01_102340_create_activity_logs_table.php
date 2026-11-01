<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('event', [
                'LOGIN',
                'LOGOUT',
                'CREATE',
                'UPDATE',
                'DELETE',
                'VOID',
                'RELEASE_BUFFER',
            ]);

            $table->string('modul', 50);
            $table->unsignedBigInteger('ref_id')->nullable();

            $table->text('keterangan')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Append-only: hanya created_at, tanpa updated_at.
            $table->timestamp('created_at')->nullable();

            $table->index(['modul', 'ref_id']);
            $table->index(['user_id', 'created_at']);
            $table->index(['event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
