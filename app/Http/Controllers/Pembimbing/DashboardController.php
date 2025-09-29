<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Peserta;
use App\Models\Presensi;
use App\Models\Perizinan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get pembimbing data
        $pembimbing = $user->pembimbing;

        if (!$pembimbing) {
            return redirect()->route('login')->with('error', 'Data pembimbing tidak ditemukan');
        }

        // Get peserta yang dibimbing oleh pembimbing ini
        // pembimbing_id di peserta merujuk ke user_id, bukan pembimbing.id
        $pesertaIds = Peserta::where('pembimbing_id', $user->id)->pluck('id');

        // Dashboard statistics
        $totalPeserta = $pesertaIds->count();

        // Presensi hari ini
        $today = now()->format('Y-m-d');
        $hadirHariIni = Presensi::whereIn('peserta_id', $pesertaIds)
            ->whereDate('tanggal', $today)
            ->where('status', 'hadir')
            ->count();

        // Izin pending
        $izinPending = Perizinan::whereIn('peserta_id', $pesertaIds)
            ->where('status', 'pending')
            ->count();

        // Tidak hadir hari ini (peserta yang tidak presensi sama sekali)
        $tidakHadir = $totalPeserta - $hadirHariIni;

        // Recent presensi (5 terbaru)
        $recentPresensi = Presensi::with('peserta')
            ->whereIn('peserta_id', $pesertaIds)
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent izin requests (5 terbaru)
        $recentIzin = Perizinan::with('peserta')
            ->whereIn('peserta_id', $pesertaIds)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Chart Data Preparation

        // 1. Weekly Attendance Chart Data (last 7 days)
        $weeklyAttendance = [];
        $weeklyTerlambat = [];
        $weeklyLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $weeklyLabels[] = $date->format('M d');

            $hadirCount = Presensi::whereIn('peserta_id', $pesertaIds)
                ->whereDate('tanggal', $date->format('Y-m-d'))
                ->where('status', 'hadir')
                ->count();

            $terlambatCount = Presensi::whereIn('peserta_id', $pesertaIds)
                ->whereDate('tanggal', $date->format('Y-m-d'))
                ->where('status', 'terlambat')
                ->count();

            $weeklyAttendance[] = $hadirCount;
            $weeklyTerlambat[] = $terlambatCount;
        }

        $totalAlpa = 0;

        foreach ($pesertaIds as $pesertaId) {
            $peserta = Peserta::find($pesertaId);

            // Ambil periode magang peserta
            $startDate = Carbon::parse($peserta->tanggal_mulai);
            $endDate   = Carbon::parse($peserta->tanggal_selesai);
            
            // Batasi perhitungan hanya sampai hari ini (tidak menghitung hari yang belum terjadi)
            $today = Carbon::now();
            if ($endDate->gt($today)) {
                $endDate = $today;
            }
            
            // Jangan hitung jika periode belum dimulai
            if ($startDate->gt($today)) {
                continue;
            }

            // Ambil semua presensi dalam periode tersebut
            $presensiDates = Presensi::where('peserta_id', $pesertaId)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->pluck('tanggal')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->toArray();

            $presensiSet = array_flip($presensiDates);

            // Hitung alpa berdasarkan hari kerja dalam periode magang
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                if ($date->isWeekend()) continue; // skip sabtu & minggu

                if (!isset($presensiSet[$date->toDateString()])) {
                    $totalAlpa++;
                }
            }
        }

        // 2. Status Distribution Chart Data (berdasarkan periode magang)
        $currentMonth = Carbon::now()->format('Y-m');
        
        // Hitung status distribusi berdasarkan periode magang yang sama dengan alpa
        $statusHadir = 0;
        $statusTerlambat = 0;
        $statusIzin = 0;
        $statusSakit = 0;
        
        foreach ($pesertaIds as $pesertaId) {
            $peserta = Peserta::find($pesertaId);
            
            // Ambil periode magang peserta
            $startDate = Carbon::parse($peserta->tanggal_mulai);
            $endDate = Carbon::parse($peserta->tanggal_selesai);
            
            // Batasi perhitungan hanya sampai hari ini
            $today = Carbon::now();
            if ($endDate->gt($today)) {
                $endDate = $today;
            }
            
            // Jangan hitung jika periode belum dimulai
            if ($startDate->gt($today)) {
                continue;
            }
            
            // Hitung status dalam periode magang
            $statusHadir += Presensi::where('peserta_id', $pesertaId)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where('status', 'hadir')
                ->count();
                
            $statusTerlambat += Presensi::where('peserta_id', $pesertaId)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where('status', 'terlambat')
                ->count();
                
            $statusIzin += Presensi::where('peserta_id', $pesertaId)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where('status', 'izin')
                ->count();
                
            $statusSakit += Presensi::where('peserta_id', $pesertaId)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where('status', 'sakit')
                ->count();
        }
        
        $statusDistribution = [
            'hadir' => $statusHadir,
            'terlambat' => $statusTerlambat,
            'izin' => $statusIzin,
            'sakit' => $statusSakit,
            'alpa' => $totalAlpa,
        ];

        // 3. Peserta Performance Chart Data (attendance percentage)
        $pesertaPerformance = [];
        $pesertaNames = [];

        foreach (Peserta::where('pembimbing_id', $user->id)->get() as $peserta) {
            // Ambil periode magang peserta
            $startDate = Carbon::parse($peserta->tanggal_mulai);
            $endDate = Carbon::parse($peserta->tanggal_selesai);
            $today = Carbon::now();
            
            // Batasi perhitungan hanya sampai hari ini
            if ($endDate->gt($today)) {
                $endDate = $today;
            }
            
            // Jangan hitung jika periode belum dimulai
            if ($startDate->gt($today)) {
                $pesertaNames[] = $peserta->nama_lengkap;
                $pesertaPerformance[] = 0;
                continue;
            }
            
            // Hitung total hari kerja dalam periode magang (kecuali weekend)
            $totalHariKerja = 0;
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                if (!$date->isWeekend()) {
                    $totalHariKerja++;
                }
            }
            
            // Hitung hari hadir (termasuk terlambat) dalam periode magang
            $hadirCount = Presensi::where('peserta_id', $peserta->id)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count();

            $attendancePercentage = $totalHariKerja > 0 ? round(($hadirCount / $totalHariKerja) * 100, 1) : 0;

            $pesertaNames[] = $peserta->nama_lengkap;
            $pesertaPerformance[] = $attendancePercentage;
        }

        return view('pembimbing.dashboard', compact(
            'user',
            'totalPeserta',
            'hadirHariIni',
            'izinPending',
            'tidakHadir',
            'recentPresensi',
            'recentIzin',
            'weeklyLabels',
            'weeklyAttendance',
            'weeklyTerlambat',
            'statusDistribution',
            'pesertaNames',
            'pesertaPerformance'
        ));
    }
}
