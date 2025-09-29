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
        Schema::table('kegiatans', function (Blueprint $table) {
            // Mengubah jam dari time menjadi jam_mulai
            $table->renameColumn('jam', 'jam_mulai');
            
            // Menambahkan field jam_selesai
            $table->time('jam_selesai')->after('jam_mulai')->nullable();
            
            // Menambahkan kategori aktivitas
            $table->enum('kategori_aktivitas', ['meeting', 'pengerjaan_tugas', 'dokumentasi', 'laporan'])
                  ->after('deskripsi')
                  ->default('pengerjaan_tugas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            // Hapus field yang ditambahkan
            $table->dropColumn(['jam_selesai', 'kategori_aktivitas']);
            
            // Kembalikan nama kolom jam
            $table->renameColumn('jam_mulai', 'jam');
        });
    }
};
