<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Peserta;
use App\Models\Pembimbing;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PesertaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil user pembimbing yang sudah ada
        $pembimbingUsers = User::where('role', 'pembimbing')->pluck('id')->toArray();
        
        $pesertaData = [
            [
                'user' => [
                    'name' => 'Andi Wijaya',
                    'email' => 'andi.wijaya@student.university.ac.id',
                    'password' => Hash::make('peserta123'),
                    'role' => 'peserta',
                ],
                'peserta' => [
                    'nim' => '2021001001',
                    'nama_lengkap' => 'Andi Wijaya',
                    'universitas' => 'Universitas Indonesia',
                    'jurusan' => 'Teknik Informatika',
                    'tanggal_mulai' => Carbon::now()->subDays(30),
                    'tanggal_selesai' => Carbon::now()->addDays(90),
                    'alamat' => 'Jl. Margonda Raya No. 123, Depok',
                    'no_telepon' => '08111111111',
                    'status' => 'aktif'
                ]
            ],
            [
                'user' => [
                    'name' => 'Sari Indah',
                    'email' => 'sari.indah@student.university.ac.id',
                    'password' => Hash::make('peserta123'),
                    'role' => 'peserta',
                ],
                'peserta' => [
                    'nim' => '2021001002',
                    'nama_lengkap' => 'Sari Indah Permata',
                    'universitas' => 'Institut Teknologi Bandung',
                    'jurusan' => 'Sistem Informasi',
                    'tanggal_mulai' => Carbon::now()->subDays(25),
                    'tanggal_selesai' => Carbon::now()->addDays(95),
                    'alamat' => 'Jl. Dago No. 456, Bandung',
                    'no_telepon' => '08222222222',
                    'status' => 'aktif'
                ]
            ],
            [
                'user' => [
                    'name' => 'Rudi Hartono',
                    'email' => 'rudi.hartono@student.university.ac.id',
                    'password' => Hash::make('peserta123'),
                    'role' => 'peserta',
                ],
                'peserta' => [
                    'nim' => '2021001003',
                    'nama_lengkap' => 'Rudi Hartono',
                    'universitas' => 'Universitas Gadjah Mada',
                    'jurusan' => 'Teknik Elektro',
                    'tanggal_mulai' => Carbon::now()->subDays(20),
                    'tanggal_selesai' => Carbon::now()->addDays(100),
                    'alamat' => 'Jl. Malioboro No. 789, Yogyakarta',
                    'no_telepon' => '08333333333',
                    'status' => 'aktif'
                ]
            ],
            [
                'user' => [
                    'name' => 'Maya Sari',
                    'email' => 'maya.sari@student.university.ac.id',
                    'password' => Hash::make('peserta123'),
                    'role' => 'peserta',
                ],
                'peserta' => [
                    'nim' => '2021001004',
                    'nama_lengkap' => 'Maya Sari Dewi',
                    'universitas' => 'Universitas Airlangga',
                    'jurusan' => 'Manajemen',
                    'tanggal_mulai' => Carbon::now()->subDays(15),
                    'tanggal_selesai' => Carbon::now()->addDays(105),
                    'alamat' => 'Jl. Dharmahusada No. 321, Surabaya',
                    'no_telepon' => '08444444444',
                    'status' => 'aktif'
                ]
            ],
            [
                'user' => [
                    'name' => 'Doni Prasetya',
                    'email' => 'doni.prasetya@student.university.ac.id',
                    'password' => Hash::make('peserta123'),
                    'role' => 'peserta',
                ],
                'peserta' => [
                    'nim' => '2021001005',
                    'nama_lengkap' => 'Doni Prasetya',
                    'universitas' => 'Universitas Padjadjaran',
                    'jurusan' => 'Akuntansi',
                    'tanggal_mulai' => Carbon::now()->subDays(10),
                    'tanggal_selesai' => Carbon::now()->addDays(110),
                    'alamat' => 'Jl. Dipatiukur No. 654, Bandung',
                    'no_telepon' => '08555555555',
                    'status' => 'aktif'
                ]
            ],
            [
                'user' => [
                    'name' => 'Fitri Amelia',
                    'email' => 'fitri.amelia@student.university.ac.id',
                    'password' => Hash::make('peserta123'),
                    'role' => 'peserta',
                ],
                'peserta' => [
                    'nim' => '2021001006',
                    'nama_lengkap' => 'Fitri Amelia',
                    'universitas' => 'Universitas Brawijaya',
                    'jurusan' => 'Psikologi',
                    'tanggal_mulai' => Carbon::now()->subDays(5),
                    'tanggal_selesai' => Carbon::now()->addDays(115),
                    'alamat' => 'Jl. Veteran No. 987, Malang',
                    'no_telepon' => '08666666666',
                    'status' => 'aktif'
                ]
            ],
            [
                'user' => [
                    'name' => 'Agus Setiawan',
                    'email' => 'agus.setiawan@student.university.ac.id',
                    'password' => Hash::make('peserta123'),
                    'role' => 'peserta',
                ],
                'peserta' => [
                    'nim' => '2020001007',
                    'nama_lengkap' => 'Agus Setiawan',
                    'universitas' => 'Universitas Diponegoro',
                    'jurusan' => 'Teknik Mesin',
                    'tanggal_mulai' => Carbon::now()->subDays(60),
                    'tanggal_selesai' => Carbon::now()->subDays(10),
                    'alamat' => 'Jl. Prof. Soedarto No. 147, Semarang',
                    'no_telepon' => '08777777777',
                    'status' => 'selesai'
                ]
            ],
            [
                'user' => [
                    'name' => 'Dewi Lestari',
                    'email' => 'dewi.lestari@student.university.ac.id',
                    'password' => Hash::make('peserta123'),
                    'role' => 'peserta',
                ],
                'peserta' => [
                    'nim' => '2020001008',
                    'nama_lengkap' => 'Dewi Lestari',
                    'universitas' => 'Universitas Hasanuddin',
                    'jurusan' => 'Komunikasi',
                    'tanggal_mulai' => Carbon::now()->subDays(45),
                    'tanggal_selesai' => Carbon::now()->addDays(75),
                    'alamat' => 'Jl. Perintis Kemerdekaan No. 258, Makassar',
                    'no_telepon' => '08888888888',
                    'status' => 'aktif'
                ]
            ]
        ];

        foreach ($pesertaData as $index => $data) {
            // Buat user terlebih dahulu
            $user = User::create($data['user']);
            
            // Kemudian buat peserta dengan user_id dan pembimbing_id
            $pesertaInfo = $data['peserta'];
            $pesertaInfo['user_id'] = $user->id;
            
            // Assign pembimbing secara acak (jika ada pembimbing)
            if (!empty($pembimbingUsers)) {
                $pesertaInfo['pembimbing_id'] = $pembimbingUsers[$index % count($pembimbingUsers)];
            }
            
            Peserta::create($pesertaInfo);
        }
    }
}
