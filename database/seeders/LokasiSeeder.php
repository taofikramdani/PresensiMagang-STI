<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lokasi;

class LokasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data lama jika ada
        Lokasi::truncate();
        
        $lokasiData = [
            [
                'nama_lokasi' => 'Kantor Pusat PLN',
                'alamat' => 'Jl. Trunojoyo Blok M I/135, Kebayoran Baru, Jakarta Selatan 12160',
                'latitude' => -6.2460,
                'longitude' => 106.8059,
                'radius' => 100,
                'keterangan' => 'Kantor pusat PT PLN (Persero) - Gedung utama',
                'is_active' => true,
            ],
            [
                'nama_lokasi' => 'PLN Area Jakarta Raya',
                'alamat' => 'Jl. Letjen S. Parman Kav. 69-70, Slipi, Jakarta Barat 11410',
                'latitude' => -6.1744,
                'longitude' => 106.7922,
                'radius' => 150,
                'keterangan' => 'Kantor PLN Area Jakarta Raya',
                'is_active' => true,
            ],
            [
                'nama_lokasi' => 'PLN UID Jabodetabek',
                'alamat' => 'Jl. Daan Mogot Km. 11, Cengkareng, Jakarta Barat 11740',
                'latitude' => -6.1664,
                'longitude' => 106.7400,
                'radius' => 200,
                'keterangan' => 'Unit Induk Distribusi Jabodetabek',
                'is_active' => true,
            ],
            [
                'nama_lokasi' => 'PLN Distribusi Jakarta Raya',
                'alamat' => 'Jl. Proklamasi No. 70, Pegangsaan, Jakarta Pusat 10320',
                'latitude' => -6.1842,
                'longitude' => 106.8467,
                'radius' => 120,
                'keterangan' => 'Kantor PLN Distribusi Jakarta Raya dan Tangerang',
                'is_active' => true,
            ],
            [
                'nama_lokasi' => 'PLTU Muara Karang',
                'alamat' => 'Jl. Pluit Karang Ayu, Penjaringan, Jakarta Utara 14450',
                'latitude' => -6.1075,
                'longitude' => 106.7850,
                'radius' => 250,
                'keterangan' => 'Pembangkit Listrik Tenaga Uap Muara Karang',
                'is_active' => true,
            ],
            [
                'nama_lokasi' => 'Training Center PLN Duri Kosambi',
                'alamat' => 'Jl. Duri Kosambi Raya, Cengkareng, Jakarta Barat 11750',
                'latitude' => -6.1589,
                'longitude' => 106.7131,
                'radius' => 100,
                'keterangan' => 'Pusat pelatihan dan pengembangan SDM PLN',
                'is_active' => false,
            ],
        ];

        foreach ($lokasiData as $data) {
            Lokasi::create($data);
        }
        
        echo "Lokasi seeder completed: " . count($lokasiData) . " locations created.\n";
    }
}
