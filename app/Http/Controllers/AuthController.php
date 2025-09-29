<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login
     */
    public function showLoginForm(): View
    {
        return view('login');
    }

    /**
     * Proses login menggunakan database MySQL dengan field name
     */
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        $name = $request->input('name');
        $password = $request->input('password');

        // Cari user di database berdasarkan name
        $user = User::where('name', $name)->first();

        // Cek apakah user ada dan password benar
        if ($user && Hash::check($password, $user->password)) {
            // Login berhasil
            Auth::login($user);
            
            // Simpan informasi session tambahan
            session([
                'user_logged_in' => true,
                'user_type' => $user->role,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'login_time' => now()
            ]);

            // Tentukan redirect URL berdasarkan role
            $redirectUrl = '/dashboard'; // default
            switch ($user->role) {
                case 'admin':
                    $redirectUrl = '/dashboard';
                    break;
                case 'pembimbing':
                    $redirectUrl = '/pembimbing/dashboard';
                    break;
                case 'peserta':
                    $redirectUrl = '/peserta/dashboard';
                    break;
                default:
                    $redirectUrl = '/dashboard';
            }

            // Jika request mengharapkan JSON (AJAX), return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login berhasil!',
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role
                    ],
                    'redirect' => url($redirectUrl)
                ]);
            }
            
            // Jika form submit biasa, redirect
            return redirect($redirectUrl)->with('success', 'Login berhasil!');
        }

        // Login gagal
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah!'
            ], 401);
        }
        
        // Form submit biasa, kembali dengan error
        return back()->withErrors([
            'name' => 'Username atau password salah!'
        ])->withInput($request->only('name'));
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        // Logout dari Laravel Auth
        Auth::logout();
        
        // Hapus session
        session()->forget(['user_logged_in', 'user_type', 'user_name', 'user_email', 'login_time']);
        
        // Invalidate session and regenerate token for security
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Check if request expects JSON (AJAX request)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil',
                'redirect' => route('login')
            ]);
        }
        
        // For regular form submission, redirect directly
        return redirect()->route('login')->with('success', 'Logout berhasil');
    }

    /**
     * Tampilkan dashboard admin
     */
    public function dashboard()
    {
        // Cek apakah user sudah login
        if (!Auth::check() || !session('user_logged_in')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu');
        }

        $user = Auth::user();
        
        // Dashboard statistics untuk admin
        $totalPeserta = \App\Models\Peserta::count();
        $totalPembimbing = \App\Models\Pembimbing::count();
        $totalPresensiHariIni = \App\Models\Presensi::whereDate('tanggal', now())->count();
        $totalIzinPending = \App\Models\Perizinan::where('status', 'pending')->count();
        
        // Chart data preparation
        
        // 1. Peserta per Pembimbing Chart
        $pembimbingChart = \App\Models\Pembimbing::with('peserta')
            ->get()
            ->map(function($pembimbing) {
                return [
                    'name' => $pembimbing->nama_lengkap,
                    'peserta_count' => $pembimbing->peserta->count()
                ];
            });
        
        $pembimbingNames = $pembimbingChart->pluck('name')->toArray();
        $pembimbingPesertaCounts = $pembimbingChart->pluck('peserta_count')->toArray();
        
        // 2. Presensi Overview (last 7 days)
        $weeklyAttendance = [];
        $weeklyLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subDays($i);
            $weeklyLabels[] = $date->format('M d');
            
            $attendanceCount = \App\Models\Presensi::whereDate('tanggal', $date)
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count();
            
            $weeklyAttendance[] = $attendanceCount;
        }
        
        // 3. Status Distribution (all time)
        $statusCounts = \App\Models\Presensi::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $statusDistribution = [
            'hadir' => $statusCounts['hadir'] ?? 0,
            'terlambat' => $statusCounts['terlambat'] ?? 0,
            'izin' => $statusCounts['izin'] ?? 0,
            'sakit' => $statusCounts['sakit'] ?? 0,
            'alpa' => $statusCounts['alpa'] ?? 0,
        ];
        
        // 4. Monthly Overview (current year)
        $monthlyData = [];
        $monthlyLabels = [];
        for ($i = 1; $i <= 12; $i++) {
            $month = \Carbon\Carbon::create(null, $i, 1);
            $monthlyLabels[] = $month->format('M');
            
            $monthlyAttendance = \App\Models\Presensi::whereYear('tanggal', now()->year)
                ->whereMonth('tanggal', $i)
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count();
            
            $monthlyData[] = $monthlyAttendance;
        }
        
        // 5. Statistik Kampus/Universitas/Sekolah
        $kampusStats = \App\Models\Peserta::select('universitas', DB::raw('count(*) as count'))
            ->whereNotNull('universitas')
            ->where('universitas', '!=', '')
            ->groupBy('universitas')
            ->orderBy('count', 'desc')
            ->get();
        
        $kampusNames = $kampusStats->pluck('universitas')->toArray();
        $kampusCounts = $kampusStats->pluck('count')->toArray();
        
        // 6. Periode Magang Statistics
        $periodeStats = \App\Models\Peserta::select(
                DB::raw('CASE 
                    WHEN DATEDIFF(tanggal_selesai, tanggal_mulai) <= 30 THEN "1 Bulan" 
                    WHEN DATEDIFF(tanggal_selesai, tanggal_mulai) <= 60 THEN "2 Bulan" 
                    WHEN DATEDIFF(tanggal_selesai, tanggal_mulai) <= 90 THEN "3 Bulan"
                    WHEN DATEDIFF(tanggal_selesai, tanggal_mulai) <= 120 THEN "4 Bulan"
                    WHEN DATEDIFF(tanggal_selesai, tanggal_mulai) <= 150 THEN "5 Bulan"
                    WHEN DATEDIFF(tanggal_selesai, tanggal_mulai) <= 180 THEN "6 Bulan"
                    ELSE "Lebih dari 6 Bulan" 
                END as periode'),
                DB::raw('count(*) as count')
            )
            ->whereNotNull('tanggal_mulai')
            ->whereNotNull('tanggal_selesai')
            ->groupBy('periode')
            ->orderBy('count', 'desc')
            ->get();
        
        $periodeLabels = $periodeStats->pluck('periode')->toArray();
        $periodeCounts = $periodeStats->pluck('count')->toArray();
        
        // 7. Distribusi Lokasi Magang
        $lokasiStats = \App\Models\Peserta::with('lokasi')
            ->select('lokasi_id', DB::raw('count(*) as count'))
            ->whereNotNull('lokasi_id')
            ->groupBy('lokasi_id')
            ->orderBy('count', 'desc')
            ->get();
        
        $lokasiNames = [];
        $lokasiCounts = [];
        foreach ($lokasiStats as $stat) {
            $lokasiNames[] = $stat->lokasi ? $stat->lokasi->nama_lokasi : 'Tidak Diketahui';
            $lokasiCounts[] = $stat->count;
        }
        
        return view('dashboard', compact(
            'user', 
            'totalPeserta', 
            'totalPembimbing', 
            'totalPresensiHariIni', 
            'totalIzinPending',
            'pembimbingNames',
            'pembimbingPesertaCounts', 
            'weeklyAttendance',
            'weeklyLabels',
            'statusDistribution',
            'monthlyData',
            'monthlyLabels',
            'kampusNames',
            'kampusCounts',
            'periodeLabels',
            'periodeCounts',
            'lokasiNames',
            'lokasiCounts'
        ));
    }
}
