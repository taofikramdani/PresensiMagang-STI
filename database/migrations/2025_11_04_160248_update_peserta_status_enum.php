<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            // Ubah enum status dari ['aktif', 'selesai', 'berhenti'] menjadi ['aktif', 'non-aktif']
            $table->enum('status', ['aktif', 'non-aktif'])->default('aktif')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            // Kembalikan ke enum status yang lama
            $table->enum('status', ['aktif', 'selesai', 'berhenti'])->default('aktif')->change();
        });
    }
};
