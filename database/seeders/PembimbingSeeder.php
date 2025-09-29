<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pembimbing;
use Illuminate\Support\Facades\Hash;

class PembimbingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pembimbingData = [
            [
                'user' => [
                    'name' => 'Dr. Ahmad Santoso',
                    'email' => 'ahmad.santoso@company.com',
                    'password' => Hash::make('pembimbing123'),
                    'role' => 'pembimbing',
                ],
                'pembimbing' => [
                    'nip' => '198501012010011001',
                    'nama_lengkap' => 'Dr. Ahmad Santoso, S.T., M.T.',
                    'jabatan' => 'Senior Manager',
                    'departemen' => 'Engineering',
                    'alamat' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                    'no_telepon' => '08123456789',
                    'email_kantor' => 'ahmad.santoso@company.com',
                    'status' => 'aktif'
                ]
            ],
            [
                'user' => [
                    'name' => 'Siti Nurhaliza',
                    'email' => 'siti.nurhaliza@company.com',
                    'password' => Hash::make('pembimbing123'),
                    'role' => 'pembimbing',
                ],
                'pembimbing' => [
                    'nip' => '198703152012012002',
                    'nama_lengkap' => 'Siti Nurhaliza, S.Kom., M.Kom.',
                    'jabatan' => 'IT Manager',
                    'departemen' => 'Information Technology',
                    'alamat' => 'Jl. Gatot Subroto No. 456, Jakarta Selatan',
                    'no_telepon' => '08234567890',
                    'email_kantor' => 'siti.nurhaliza@company.com',
                    'status' => 'aktif'
                ]
            ],
            [
                'user' => [
                    'name' => 'Budi Prasetyo',
                    'email' => 'budi.prasetyo@company.com',
                    'password' => Hash::make('pembimbing123'),
                    'role' => 'pembimbing',
                ],
                'pembimbing' => [
                    'nip' => '198906202015031003',
                    'nama_lengkap' => 'Budi Prasetyo, S.E., M.B.A.',
                    'jabatan' => 'Finance Manager',
                    'departemen' => 'Finance & Accounting',
                    'alamat' => 'Jl. Thamrin No. 789, Jakarta Pusat',
                    'no_telepon' => '08345678901',
                    'email_kantor' => 'budi.prasetyo@company.com',
                    'status' => 'aktif'
                ]
            ],
            [
                'user' => [
                    'name' => 'Linda Sari',
                    'email' => 'linda.sari@company.com',
                    'password' => Hash::make('pembimbing123'),
                    'role' => 'pembimbing',
                ],
                'pembimbing' => [
                    'nip' => '199002102017012004',
                    'nama_lengkap' => 'Linda Sari, S.Psi., M.Psi.',
                    'jabatan' => 'HR Manager',
                    'departemen' => 'Human Resources',
                    'alamat' => 'Jl. Kuningan No. 321, Jakarta Selatan',
                    'no_telepon' => '08456789012',
                    'email_kantor' => 'linda.sari@company.com',
                    'status' => 'aktif'
                ]
            ],
            [
                'user' => [
                    'name' => 'Eko Wijaya',
                    'email' => 'eko.wijaya@company.com',
                    'password' => Hash::make('pembimbing123'),
                    'role' => 'pembimbing',
                ],
                'pembimbing' => [
                    'nip' => '198412152018011005',
                    'nama_lengkap' => 'Eko Wijaya, S.T., M.T.',
                    'jabatan' => 'Operations Manager',
                    'departemen' => 'Operations',
                    'alamat' => 'Jl. Rasuna Said No. 654, Jakarta Selatan',
                    'no_telepon' => '08567890123',
                    'email_kantor' => 'eko.wijaya@company.com',
                    'status' => 'aktif'
                ]
            ]
        ];

        foreach ($pembimbingData as $data) {
            // Buat user terlebih dahulu
            $user = User::create($data['user']);
            
            // Kemudian buat pembimbing dengan user_id
            $pembimbingInfo = $data['pembimbing'];
            $pembimbingInfo['user_id'] = $user->id;
            
            Pembimbing::create($pembimbingInfo);
        }
    }
}
