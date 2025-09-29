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
        Schema::table('pembimbing', function (Blueprint $table) {
            // Add any columns that might be needed
            if (!Schema::hasColumn('pembimbing', 'status')) {
                $table->enum('status', ['aktif', 'tidak_aktif'])->default('aktif');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembimbing', function (Blueprint $table) {
            if (Schema::hasColumn('pembimbing', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};