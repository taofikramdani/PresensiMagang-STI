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
        Schema::table('presensis', function (Blueprint $table) {
            $table->boolean('manual_entry')->default(false)->after('keterangan');
            $table->unsignedBigInteger('pengajuan_presensi_id')->nullable()->after('manual_entry');
            
            $table->foreign('pengajuan_presensi_id')
                  ->references('id')
                  ->on('pengajuan_presensis')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropForeign(['pengajuan_presensi_id']);
            $table->dropColumn(['manual_entry', 'pengajuan_presensi_id']);
        });
    }
};
