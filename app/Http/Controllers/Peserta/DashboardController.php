<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
 
class DashboardController extends Controller
{
    public function index()
    {
        // Debug untuk memastikan method ini dipanggil
        Log::info('Peserta Dashboard accessed by user: ' . Auth::id());
        
        $user = Auth::user();
        $today = Carbon::today();
        
        // Get today's attendance
        $presensiHariIni = null; // Will be implemented when Presensi model exists
        
        // Get working hours from database based on today
        $hariMap = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu'
        ];
        
        $hariInggris = strtolower(Carbon::now('Asia/Jakarta')->format('l'));
        $hariIni = $hariMap[$hariInggris] ?? $hariInggris; // Convert to Indonesian format
        
        $jamKerja = \App\Models\JamKerja::where('is_active', true)
            ->get()
            ->first(function ($jk) use ($hariIni) {
                $hariKerja = $jk->hari_kerja ?? [];
                return in_array($hariIni, $hariKerja);
            });
        
        // Fallback ke jam kerja aktif pertama jika tidak ada yang cocok
        if (!$jamKerja) {
            $jamKerja = \App\Models\JamKerja::aktif()->first();
        }
        
        // Get month statistics
        $statistik = [
            'hadir' => 15, // Mock data
            'total_hari_kerja' => 22,
            'terlambat' => 2,
            'izin' => 1
        ];
        
        return view('peserta.dashboard', compact('presensiHariIni', 'statistik', 'jamKerja'));
    }
}
