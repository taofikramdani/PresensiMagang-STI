<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->string('group')->default('general'); // general, presensi, system
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            [
                'key' => 'enable_gps_tracking',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'presensi',
                'label' => 'Aktifkan GPS Tracking',
                'description' => 'Mengharuskan peserta menggunakan lokasi GPS saat melakukan presensi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enable_photo_capture',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'presensi',
                'label' => 'Aktifkan Foto Presensi',
                'description' => 'Mengharuskan peserta mengambil foto saat melakukan presensi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'force_https_for_features',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'system',
                'label' => 'Paksa HTTPS untuk Fitur GPS & Foto',
                'description' => 'Jika diaktifkan, fitur GPS dan Foto hanya tersedia di koneksi HTTPS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
