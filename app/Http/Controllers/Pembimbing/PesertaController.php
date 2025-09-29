<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Peserta;
use App\Models\Presensi;
use App\Models\Perizinan;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;


class PesertaController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $pembimbing = $user->pembimbing;
        
        if (!$pembimbing) {
            return redirect()->route('login')->with('error', 'Data pembimbing tidak ditemukan');
        }
        
        // Update status peserta yang sudah melewati periode magang
        Peserta::where('status', 'aktif')
            ->whereDate('tanggal_selesai', '<', now())
            ->update(['status' => 'non-aktif']);

        // Base query untuk peserta yang dibimbing
        $query = Peserta::where('pembimbing_id', $user->id);
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('periode')) {
            // Implement periode filter logic based on your needs
            // Example: filter by semester/year
        }
        
        $peserta = $query->with('user')->orderBy('nama_lengkap')->paginate(10);
        
        // Add status hari ini untuk setiap peserta
        $today = now()->format('Y-m-d');
        foreach ($peserta as $p) {
            // Cek presensi hari ini
            $presensiHariIni = Presensi::where('peserta_id', $p->id)
                ->whereDate('tanggal', $today)
                ->first();
            
            // Cek perizinan hari ini yang disetujui
            $perizinanHariIni = Perizinan::where('peserta_id', $p->id)
                ->where('status', 'disetujui')
                ->whereDate('tanggal', $today)
                ->first();
            
            if ($presensiHariIni) {
                // Jika ada presensi, gunakan status dari presensi
                $p->status_hari_ini = $presensiHariIni->status;
            } elseif ($perizinanHariIni) {
                // Jika ada perizinan yang disetujui, gunakan jenis perizinan
                $p->status_hari_ini = $perizinanHariIni->jenis; // 'izin' atau 'sakit'
            } else {
                // Jika tidak ada presensi dan tidak ada perizinan
                $p->status_hari_ini = 'tidak_hadir';
            }
        }
        
        // Statistics - hitung berdasarkan status yang sudah ditetapkan
        $totalPeserta = Peserta::where('pembimbing_id', $user->id)->count();
        $pesertaAktif = Peserta::where('pembimbing_id', $user->id)->where('status', 'aktif')->count();
        
        // Hitung hadir hari ini dari data yang sudah diproses - terlambat dihitung sebagai hadir
        $hadirHariIni = 0;
        $terlambatHariIni = 0;
        $tidakHadir = 0;
        
        foreach ($peserta as $p) {
            if ($p->status_hari_ini == 'hadir') {
                $hadirHariIni++;
            } elseif ($p->status_hari_ini == 'terlambat') {
                $terlambatHariIni++;
                $hadirHariIni++; // Terlambat tetap dihitung sebagai hadir
            } elseif ($p->status_hari_ini == 'tidak_hadir') {
                $tidakHadir++;
            }
        }
        
        return view('pembimbing.peserta.index', compact(
            'peserta',
            'totalPeserta',
            'pesertaAktif',
            'hadirHariIni',
            'terlambatHariIni',
            'tidakHadir'
        ));
    }
    
    public function show(Peserta $peserta, Request $request)
    {
        $user = Auth::user();
        $pembimbing = $user->pembimbing;
        
        // Pastikan peserta ini dibimbing oleh pembimbing yang login
        if ($peserta->pembimbing_id !== $user->id) {
            abort(403, 'Unauthorized access');
        }
        
        // Load user relationship for email
        $peserta->load('user');
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'peserta' => $peserta
            ]);
        }
        
        return view('pembimbing.peserta.show', compact('peserta'));
    }

    public function exportExcel()
    {
        try {
            $user = Auth::user();
            $pembimbing = $user->pembimbing;
            
            if (!$pembimbing) {
                return response()->json(['error' => 'Data pembimbing tidak ditemukan'], 404);
            }

            // Get all peserta data with status for today
            $peserta = Peserta::where('pembimbing_id', $user->id)->with('user')->get();
            
            $today = now()->format('Y-m-d');
            foreach ($peserta as $p) {
                $presensiHariIni = Presensi::where('peserta_id', $p->id)
                    ->whereDate('tanggal', $today)
                    ->first();
                
                $perizinanHariIni = Perizinan::where('peserta_id', $p->id)
                    ->where('status', 'disetujui')
                    ->whereDate('tanggal', $today)
                    ->first();
                
                if ($presensiHariIni) {
                    $p->status_hari_ini = $presensiHariIni->status;
                } elseif ($perizinanHariIni) {
                    $p->status_hari_ini = $perizinanHariIni->jenis;
                } else {
                    $p->status_hari_ini = 'tidak_hadir';
                }
            }

            // Generate HTML table for Excel
            $fileName = 'daftar-peserta-magang-' . str_replace(' ', '-', $user->name) . '-' . Carbon::now()->format('Y-m-d') . '.xls';
            
            return response()->view('pembimbing.peserta.excel', [
                'peserta' => $peserta,
                'pembimbing' => $user,
                'tanggal' => Carbon::now()
            ])->header('Content-Type', 'application/vnd.ms-excel')
              ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengekspor Excel: ' . $e->getMessage()], 500);
        }
    }

    public function exportPdf()
    {
        try {
            $user = Auth::user();
            $pembimbing = $user->pembimbing;
            
            if (!$pembimbing) {
                return response()->json(['error' => 'Data pembimbing tidak ditemukan'], 404);
            }

            // Get all peserta data with status for today
            $peserta = Peserta::where('pembimbing_id', $user->id)->with('user')->get();
            
            $today = now()->format('Y-m-d');
            foreach ($peserta as $p) {
                $presensiHariIni = Presensi::where('peserta_id', $p->id)
                    ->whereDate('tanggal', $today)
                    ->first();
                
                $perizinanHariIni = Perizinan::where('peserta_id', $p->id)
                    ->where('status', 'disetujui')
                    ->whereDate('tanggal', $today)
                    ->first();
                
                if ($presensiHariIni) {
                    $p->status_hari_ini = $presensiHariIni->status;
                } elseif ($perizinanHariIni) {
                    $p->status_hari_ini = $perizinanHariIni->jenis;
                } else {
                    $p->status_hari_ini = 'tidak_hadir';
                }
            }

            // Generate PDF
            $pdf = Pdf::loadView('pembimbing.peserta.pdf', [
                'peserta' => $peserta,
                'pembimbing' => $user,
                'tanggal' => Carbon::now(),
                'periode' => 'Daftar Peserta Magang'
            ]);

            $fileName = 'daftar-peserta-magang-' . $user->name . '-' . Carbon::now()->format('Y-m-d') . '.pdf';
            
            return $pdf->download($fileName);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengekspor PDF: ' . $e->getMessage()], 500);
        }
    }
}