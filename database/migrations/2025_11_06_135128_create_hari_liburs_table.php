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
        Schema::create('hari_liburs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_libur'); // Nama hari libur
            $table->date('tanggal'); // Tanggal libur
            $table->text('deskripsi')->nullable(); // Deskripsi hari libur
            $table->enum('jenis', ['nasional', 'keagamaan', 'custom'])->default('nasional'); // Jenis hari libur
            $table->string('warna', 7)->default('#ff0000'); // Warna untuk calendar (hex color)
            $table->boolean('is_active')->default(true); // Status aktif/non-aktif
            $table->timestamps();
            
            // Index untuk performa query
            $table->index('tanggal');
            $table->index(['tanggal', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hari_liburs');
    }
};
