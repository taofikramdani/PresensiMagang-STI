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
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke peserta
            $table->foreignId('peserta_id')->constrained('peserta')->onDelete('cascade');
            
            // Tanggal presensi
            $table->date('tanggal');
            
            // Jam kerja yang berlaku
            $table->foreignId('jam_kerja_id')->constrained('jam_kerja')->onDelete('cascade');
            
            // Lokasi presensi
            $table->foreignId('lokasi_id')->constrained('lokasis')->onDelete('cascade');
            
            // Data presensi masuk
            $table->time('jam_masuk')->nullable();
            $table->decimal('latitude_masuk', 10, 8)->nullable();
            $table->decimal('longitude_masuk', 11, 8)->nullable();
            $table->string('foto_masuk')->nullable();
            $table->text('keterangan_masuk')->nullable();
            
            // Data presensi keluar
            $table->time('jam_keluar')->nullable();
            $table->decimal('latitude_keluar', 10, 8)->nullable();
            $table->decimal('longitude_keluar', 11, 8)->nullable();
            $table->string('foto_keluar')->nullable();
            $table->text('keterangan_keluar')->nullable();
            
            // Status presensi
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'])->default('hadir');
            
            // Durasi kerja (dalam menit)
            $table->integer('durasi_kerja')->nullable();
            
            // Keterlambatan (dalam menit)
            $table->integer('keterlambatan')->default(0);
            
            // Keterangan tambahan
            $table->text('keterangan')->nullable();
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['peserta_id', 'tanggal']);
            $table->index('tanggal');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};
