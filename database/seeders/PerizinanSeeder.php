<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Perizinan;
use App\Models\Peserta;
use App\Models\Pembimbing;
use Carbon\Carbon;

class PerizinanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pesertas = Peserta::all();
        $pembimbings = Pembimbing::all();
        
        if ($pesertas->isEmpty() || $pembimbings->isEmpty()) {
            $this->command->warn('Tidak ada data peserta atau pembimbing. Jalankan seeder untuk peserta dan pembimbing terlebih dahulu.');
            return;
        }

        $perizinanData = [
            // Perizinan pending
            [
                'peserta_id' => $pesertas->first()->id,
                'jenis' => 'izin',
                'tanggal' => Carbon::now()->addDays(1),
                'keterangan' => 'Izin keperluan keluarga yang mendesak. Akan menghadiri acara pernikahan saudara di luar kota.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'peserta_id' => $pesertas->skip(1)->first()->id ?? $pesertas->first()->id,
                'jenis' => 'sakit',
                'tanggal' => Carbon::now()->addDays(2),
                'keterangan' => 'Sakit demam dan flu. Sudah ke dokter dan disarankan untuk istirahat total.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(1),
            ],
            
            // Perizinan yang sudah disetujui
            [
                'peserta_id' => $pesertas->first()->id,
                'jenis' => 'izin',
                'tanggal' => Carbon::now()->subDays(5),
                'keterangan' => 'Izin untuk mengurus dokumen penting di kantor kelurahan.',
                'status' => 'disetujui',
                'pembimbing_id' => $pembimbings->first()->id,
                'catatan_pembimbing' => 'Disetujui. Segera selesaikan urusan dokumen tersebut.',
                'tanggal_approval' => Carbon::now()->subDays(5)->addHours(2),
                'created_at' => Carbon::now()->subDays(5)->subHours(1),
            ],
            [
                'peserta_id' => $pesertas->skip(1)->first()->id ?? $pesertas->first()->id,
                'jenis' => 'sakit',
                'tanggal' => Carbon::now()->subDays(10),
                'keterangan' => 'Sakit kepala migrain yang cukup parah dan tidak bisa beraktivitas.',
                'status' => 'disetujui',
                'pembimbing_id' => $pembimbings->first()->id,
                'catatan_pembimbing' => 'Disetujui. Pastikan untuk istirahat yang cukup dan minum obat sesuai anjuran dokter.',
                'tanggal_approval' => Carbon::now()->subDays(10)->addHours(1),
                'created_at' => Carbon::now()->subDays(10)->subHours(2),
            ],
            
            // Perizinan yang ditolak
            [
                'peserta_id' => $pesertas->first()->id,
                'jenis' => 'izin',
                'tanggal' => Carbon::now()->subDays(15),
                'keterangan' => 'Izin untuk liburan bersama teman-teman ke pantai.',
                'status' => 'ditolak',
                'pembimbing_id' => $pembimbings->first()->id,
                'catatan_pembimbing' => 'Maaf, perizinan untuk keperluan liburan tidak dapat disetujui karena masih dalam periode magang yang aktif. Harap fokus pada kegiatan magang.',
                'tanggal_approval' => Carbon::now()->subDays(15)->addHours(3),
                'created_at' => Carbon::now()->subDays(15)->subHours(1),
            ],
        ];

        // Tambah data perizinan bulan lalu untuk statistik
        if ($pesertas->count() >= 2) {
            $perizinanData[] = [
                'peserta_id' => $pesertas->skip(1)->first()->id,
                'jenis' => 'sakit',
                'tanggal' => Carbon::now()->subMonth()->addDays(5),
                'keterangan' => 'Sakit diare dan mual-mual setelah makan makanan yang tidak cocok.',
                'status' => 'disetujui',
                'pembimbing_id' => $pembimbings->first()->id,
                'catatan_pembimbing' => 'Disetujui. Jaga pola makan dan pilih makanan yang bersih.',
                'tanggal_approval' => Carbon::now()->subMonth()->addDays(5)->addHours(1),
                'created_at' => Carbon::now()->subMonth()->addDays(5)->subHours(1),
            ];
        }

        foreach ($perizinanData as $data) {
            Perizinan::create($data);
        }

        $this->command->info('Data perizinan berhasil ditambahkan: ' . count($perizinanData) . ' record');
    }
}
