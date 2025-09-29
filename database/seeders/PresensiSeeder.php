<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Presensi;
use App\Models\Peserta;
use App\Models\JamKerja;
use App\Models\Lokasi;
use Carbon\Carbon;

class PresensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $peserta = Peserta::first();
        $jamKerja = JamKerja::first();
        $lokasi = Lokasi::first();

        if (!$peserta || !$jamKerja || !$lokasi) {
            $this->command->info('Pastikan data peserta, jam kerja, dan lokasi sudah ada sebelum menjalankan seeder ini.');
            return;
        }

        // Data presensi untuk 10 hari terakhir
        for ($i = 9; $i >= 0; $i--) {
            $tanggal = Carbon::now()->subDays($i)->format('Y-m-d');
            
            // Skip weekend jika jam kerja tidak mencakup weekend
            $hariKerja = $jamKerja->hari_kerja ?? [];
            if (!is_array($hariKerja)) {
                $hariKerja = is_string($hariKerja) ? json_decode($hariKerja, true) ?? [] : [];
            }
            
            $namaHari = Carbon::parse($tanggal)->format('l');
            
            // Mapping hari bahasa Inggris ke Indonesia
            $hariMap = [
                'Monday' => 'senin',
                'Tuesday' => 'selasa',
                'Wednesday' => 'rabu',
                'Thursday' => 'kamis',
                'Friday' => 'jumat',
                'Saturday' => 'sabtu',
                'Sunday' => 'minggu'
            ];
            
            $hariIndo = $hariMap[$namaHari];
            
            if (!in_array($hariIndo, $hariKerja)) {
                continue; // Skip hari yang tidak termasuk hari kerja
            }

            // Variasi waktu masuk dan keluar
            $jamMasukDasar = Carbon::parse($jamKerja->jam_masuk);
            $jamKeluarDasar = Carbon::parse($jamKerja->jam_keluar);
            
            // Random keterlambatan 0-30 menit
            $keterlambatan = rand(0, 30);
            $jamMasuk = $jamMasukDasar->copy()->addMinutes($keterlambatan);
            
            // Random durasi kerja normal + overtime
            $durasiNormal = $jamMasukDasar->diffInMinutes($jamKeluarDasar);
            $overtime = rand(-30, 60); // -30 menit sampai +60 menit
            $jamKeluar = $jamMasuk->copy()->addMinutes($durasiNormal + $overtime);
            
            // Tentukan status
            $status = 'hadir';
            if ($keterlambatan > ($jamKerja->toleransi_keterlambatan ?? 15)) {
                $status = 'terlambat';
            }
            
            // Random untuk izin/sakit (5% kemungkinan)
            if (rand(1, 100) <= 5) {
                $status = rand(0, 1) ? 'izin' : 'sakit';
                $jamMasuk = null;
                $jamKeluar = null;
                $keterlambatan = 0;
            }

            $presensi = Presensi::create([
                'peserta_id' => $peserta->id,
                'tanggal' => $tanggal,
                'jam_kerja_id' => $jamKerja->id,
                'lokasi_id' => $lokasi->id,
                'jam_masuk' => $jamMasuk?->format('H:i:s'),
                'latitude_masuk' => $jamMasuk ? $lokasi->latitude + (rand(-10, 10) / 10000) : null,
                'longitude_masuk' => $jamMasuk ? $lokasi->longitude + (rand(-10, 10) / 10000) : null,
                'jam_keluar' => $jamKeluar?->format('H:i:s'),
                'latitude_keluar' => $jamKeluar ? $lokasi->latitude + (rand(-10, 10) / 10000) : null,
                'longitude_keluar' => $jamKeluar ? $lokasi->longitude + (rand(-10, 10) / 10000) : null,
                'status' => $status,
                'durasi_kerja' => $jamMasuk && $jamKeluar ? $jamMasuk->diffInMinutes($jamKeluar) : null,
                'keterlambatan' => $keterlambatan,
                'keterangan' => $status === 'izin' ? 'Izin keperluan keluarga' : 
                              ($status === 'sakit' ? 'Sakit demam' : null),
            ]);
        }

        // Presensi hari ini (belum keluar)
        $today = Carbon::now()->format('Y-m-d');
        $hariIni = Carbon::now()->format('l');
        $hariIndoHariIni = [
            'Monday' => 'senin',
            'Tuesday' => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday' => 'kamis',
            'Friday' => 'jumat',
            'Saturday' => 'sabtu',
            'Sunday' => 'minggu'
        ][$hariIni];
        
        if (in_array($hariIndoHariIni, is_array($jamKerja->hari_kerja) ? $jamKerja->hari_kerja : [])) {
            $jamMasukHariIni = Carbon::parse($jamKerja->jam_masuk)->addMinutes(rand(0, 15));
            
            Presensi::create([
                'peserta_id' => $peserta->id,
                'tanggal' => $today,
                'jam_kerja_id' => $jamKerja->id,
                'lokasi_id' => $lokasi->id,
                'jam_masuk' => $jamMasukHariIni->format('H:i:s'),
                'latitude_masuk' => $lokasi->latitude + (rand(-10, 10) / 10000),
                'longitude_masuk' => $lokasi->longitude + (rand(-10, 10) / 10000),
                'status' => 'hadir',
                'keterlambatan' => max(0, $jamMasukHariIni->diffInMinutes(Carbon::parse($jamKerja->jam_masuk))),
            ]);
        }

        $this->command->info('Data presensi berhasil dibuat.');
    }
}
