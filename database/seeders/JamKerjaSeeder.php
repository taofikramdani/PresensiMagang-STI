<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\JamKerja;

class JamKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Hapus data lama yang mungkin bermasalah
        JamKerja::truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $jamKerjaData = [
            [
                'nama_shift' => 'Normal (Senin-Kamis)',
                'jam_masuk' => '07:30',
                'jam_keluar' => '16:00',
                'hari_kerja' => ['monday', 'tuesday', 'wednesday', 'thursday'],
                'toleransi_keterlambatan' => 15,
                'keterangan' => 'Jam kerja normal Senin - Kamis',
                'is_active' => true,
            ],
            [
                'nama_shift' => 'Normal (Jumat)',
                'jam_masuk' => '07:00',
                'jam_keluar' => '17:00',
                'hari_kerja' => ['friday'],
                'toleransi_keterlambatan' => 15,
                'keterangan' => 'Jam kerja hari Jumat',
                'is_active' => true,
            ],
        ];

        foreach ($jamKerjaData as $data) {
            JamKerja::create($data);
        }
    }
}
