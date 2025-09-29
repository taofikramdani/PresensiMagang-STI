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
        Schema::create('jam_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('nama_shift')->comment('Nama shift kerja');
            $table->time('jam_masuk')->comment('Jam masuk kerja');
            $table->time('jam_keluar')->comment('Jam keluar kerja');
            $table->json('hari_kerja')->comment('Array hari kerja dalam format JSON');
            $table->integer('toleransi_keterlambatan')->default(15)->comment('Toleransi keterlambatan dalam menit');
            $table->text('keterangan')->nullable()->comment('Keterangan tambahan');
            $table->boolean('is_active')->default(true)->comment('Status aktif jam kerja');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jam_kerja');
    }
};
