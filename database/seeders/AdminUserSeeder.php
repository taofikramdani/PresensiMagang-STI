<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus user admin yang mungkin sudah ada
        DB::table('users')->where('email', 'admin@presensi.com')->delete();

        // Insert user admin dengan password yang jelas
        DB::table('users')->insert([
            'name' => 'Administrator',
            'email' => 'admin@presensi.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "Admin user created:\n";
        echo "Email: admin@presensi.com\n";
        echo "Password: admin123\n";
    }
}
