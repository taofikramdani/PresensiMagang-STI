<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Peserta;
use App\Models\Presensi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class KehadiranController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $pembimbing = $user->pembimbing;
        
        if (!$pembimbing) {
            return redirect()->route('login')->with('error', 'Data pembimbing tidak ditemukan');
        }
        
        // Get peserta list untuk filter
        $pesertaList = Peserta::where('pembimbing_id', $user->id)
            ->orderBy('nama_lengkap')
            ->get();
        
        // Base query untuk presensi
        $pesertaIds = $pesertaList->pluck('id');
        $query = Presensi::with(['peserta', 'lokasi'])
            ->whereIn('peserta_id', $pesertaIds);
        
        // Apply filters
        if ($request->filled('peserta')) {
            $query->where('peserta_id', $request->peserta);
        }
        
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_akhir);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $presensiList = $query->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Statistics - perbaiki perhitungan statistik
        $totalPresensi = Presensi::whereIn('peserta_id', $pesertaIds)->count();
        $totalHadir = Presensi::whereIn('peserta_id', $pesertaIds)
            ->where('status', 'hadir')
            ->count();
        $totalTepat = $totalHadir; // tepat waktu = hadir tanpa terlambat
        $totalTerlambat = Presensi::whereIn('peserta_id', $pesertaIds)->where('status', 'terlambat')->count();
        $totalIzin = Presensi::whereIn('peserta_id', $pesertaIds)->where('status', 'izin')->count();
        $totalSakit = Presensi::whereIn('peserta_id', $pesertaIds)->where('status', 'sakit')->count();
        $totalAlpa = Presensi::whereIn('peserta_id', $pesertaIds)
            ->whereIn('status', ['alpha', 'alpa'])
            ->count();
        
        // Presensi hari ini
        $today = now()->format('Y-m-d');
        $presensiHariIni = Presensi::with(['peserta', 'lokasi'])
            ->whereIn('peserta_id', $pesertaIds)
            ->whereDate('tanggal', $today)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('pembimbing.kehadiran.index', compact(
            'pesertaList',
            'presensiList',
            'presensiHariIni',
            'totalPresensi',
            'totalHadir',
            'totalTepat',
            'totalTerlambat',
            'totalIzin',
            'totalSakit',
            'totalAlpa'
        ));
    }
    
    public function show(Presensi $presensi)
    {
        $user = Auth::user();
        $pembimbing = $user->pembimbing;
        
        // Pastikan presensi ini milik peserta yang dibimbing
        if ($presensi->peserta->pembimbing_id !== $user->id) {
            abort(403, 'Unauthorized access');
        }
        
        // Load relationships
        $presensi->load(['peserta.user', 'lokasi', 'jamKerja']);
        
        return view('pembimbing.kehadiran.detail', compact('presensi'));
    }

    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        $pembimbing = $user->pembimbing;

        if (!$pembimbing) {
            return redirect()->route('login')->with('error', 'Data pembimbing tidak ditemukan');
        }

        // Build query dengan filter yang sama seperti index
        $pesertaList = Peserta::where('pembimbing_id', $user->id)->get();
        $pesertaIds = $pesertaList->pluck('id');
        
        $query = Presensi::with(['peserta', 'lokasi', 'jamKerja'])
            ->whereIn('peserta_id', $pesertaIds);

        // Apply filters
        if ($request->filled('peserta')) {
            $query->where('peserta_id', $request->peserta);
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_akhir);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $presensiList = $query->orderBy('tanggal', 'desc')
                             ->orderBy('jam_masuk', 'desc')
                             ->get();

        // Get statistics
        $totalPresensi = $presensiList->count();
        $totalTepat = $presensiList->where('status', 'hadir')->count();
        $totalTerlambat = $presensiList->where('status', 'terlambat')->count();
        $totalIzin = $presensiList->where('status', 'izin')->count();
        $totalSakit = $presensiList->where('status', 'sakit')->count();
        $totalAlpa = $presensiList->where('status', 'alpa')->count();

        // Get filter info
        $selectedPeserta = null;
        if ($request->filled('peserta')) {
            $selectedPeserta = Peserta::find($request->peserta);
        }

        $periode = '';
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $periode = Carbon::parse($request->tanggal_mulai)->format('d/m/Y') . ' - ' . 
                       Carbon::parse($request->tanggal_akhir)->format('d/m/Y');
        } elseif ($request->filled('tanggal_mulai')) {
            $periode = 'Sejak ' . Carbon::parse($request->tanggal_mulai)->format('d/m/Y');
        } elseif ($request->filled('tanggal_akhir')) {
            $periode = 'Sampai ' . Carbon::parse($request->tanggal_akhir)->format('d/m/Y');
        } else {
            $periode = 'Semua Periode';
        }

        $filename = 'rekap-kehadiran-' . 
                   ($selectedPeserta ? $selectedPeserta->nama_lengkap : 'semua-peserta') . '-' . 
                   now()->format('Y-m-d') . '.xls';

        return response()->view('pembimbing.kehadiran.excel', compact(
            'presensiList', 'pembimbing', 'selectedPeserta', 'periode',
            'totalPresensi', 'totalTepat', 'totalTerlambat', 'totalIzin', 'totalSakit', 'totalAlpa'
        ))->header('Content-Type', 'application/vnd.ms-excel')
          ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $pembimbing = $user->pembimbing;

        if (!$pembimbing) {
            return redirect()->route('login')->with('error', 'Data pembimbing tidak ditemukan');
        }

        // Build query dengan filter yang sama seperti index
        $pesertaList = Peserta::where('pembimbing_id', $user->id)->get();
        $pesertaIds = $pesertaList->pluck('id');
        
        $query = Presensi::with(['peserta', 'lokasi', 'jamKerja'])
            ->whereIn('peserta_id', $pesertaIds);

        // Apply filters
        if ($request->filled('peserta')) {
            $query->where('peserta_id', $request->peserta);
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_akhir);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Jika ada filter peserta spesifik, generate data lengkap seperti peserta riwayat
        if ($request->filled('peserta') && $request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $selectedPesertaModel = Peserta::find($request->peserta);
            $startDate = Carbon::parse($request->tanggal_mulai);
            $endDate = Carbon::parse($request->tanggal_akhir);
            
            // Batasi berdasarkan periode magang peserta jika ada
            if ($selectedPesertaModel->tanggal_mulai && $selectedPesertaModel->tanggal_selesai) {
                $tanggalMulaiMagang = Carbon::parse($selectedPesertaModel->tanggal_mulai);
                $tanggalSelesaiMagang = Carbon::parse($selectedPesertaModel->tanggal_selesai);
                
                if ($startDate->lt($tanggalMulaiMagang)) {
                    $startDate = $tanggalMulaiMagang->copy();
                }
                if ($endDate->gt($tanggalSelesaiMagang)) {
                    $endDate = $tanggalSelesaiMagang->copy();
                }
            }
            
            // Get existing presensi data
            $existingPresensi = $query->orderBy('tanggal', 'asc')->get()
                ->keyBy(function ($item) {
                    return Carbon::parse($item->tanggal)->toDateString();
                });
            
            // Generate complete data dengan alpha/kosong
            $presensiList = collect();
            $today = Carbon::now('Asia/Jakarta')->toDateString();
            
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                $tanggalString = $currentDate->toDateString();
                
                // Skip weekend (Sabtu=6, Minggu=0)
                if (!in_array($currentDate->dayOfWeek, [0, 6])) {
                    if ($existingPresensi->has($tanggalString)) {
                        // Ada data presensi
                        $presensiList->push($existingPresensi[$tanggalString]);
                    } else {
                        // Tidak ada data presensi
                        if ($tanggalString <= $today) {
                            // Tanggal sudah lewat atau hari ini, buat entry alpha
                            $alphaEntry = new \stdClass();
                            $alphaEntry->tanggal = $currentDate->copy();
                            $alphaEntry->peserta = $selectedPesertaModel;
                            $alphaEntry->jam_masuk = null;
                            $alphaEntry->jam_keluar = null;
                            $alphaEntry->status = 'alpha';
                            $alphaEntry->keterangan = 'Tidak ada data presensi';
                            $alphaEntry->lokasi = $selectedPesertaModel->lokasi;
                            $alphaEntry->is_alpha_entry = true;
                            
                            $presensiList->push($alphaEntry);
                        } else {
                            // Tanggal belum datang, buat entry kosong
                            $emptyEntry = new \stdClass();
                            $emptyEntry->tanggal = $currentDate->copy();
                            $emptyEntry->peserta = $selectedPesertaModel;
                            $emptyEntry->jam_masuk = null;
                            $emptyEntry->jam_keluar = null;
                            $emptyEntry->status = null;
                            $emptyEntry->keterangan = null;
                            $emptyEntry->lokasi = $selectedPesertaModel->lokasi;
                            $emptyEntry->is_future_entry = true;
                            
                            $presensiList->push($emptyEntry);
                        }
                    }
                }
                
                $currentDate->addDay();
            }
        } else {
            // Mode biasa, hanya tampilkan data yang ada
            $presensiList = $query->orderBy('tanggal', 'asc')
                                 ->orderBy('jam_masuk', 'asc')
                                 ->get();
        }

        // Get statistics
        $totalPresensi = $presensiList->count();
        $totalTepat = $presensiList->where('status', 'hadir')->count();
        $totalTerlambat = $presensiList->where('status', 'terlambat')->count();
        $totalIzin = $presensiList->where('status', 'izin')->count();
        $totalSakit = $presensiList->where('status', 'sakit')->count();
        $totalAlpa = $presensiList->where('status', 'alpha')->count();

        // Get filter info
        $selectedPeserta = null;
        if ($request->filled('peserta')) {
            $selectedPeserta = Peserta::find($request->peserta);
        }

        $periode = '';
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $periode = Carbon::parse($request->tanggal_mulai)->format('d/m/Y') . ' - ' . 
                       Carbon::parse($request->tanggal_akhir)->format('d/m/Y');
        } elseif ($request->filled('tanggal_mulai')) {
            $periode = 'Sejak ' . Carbon::parse($request->tanggal_mulai)->format('d/m/Y');
        } elseif ($request->filled('tanggal_akhir')) {
            $periode = 'Sampai ' . Carbon::parse($request->tanggal_akhir)->format('d/m/Y');
        } else {
            $periode = 'Semua Periode';
        }

        $filename = 'rekap-kehadiran-' . 
                   ($selectedPeserta ? $selectedPeserta->nama_lengkap : 'semua-peserta') . '-' . 
                   now()->format('Y-m-d') . '.pdf';

        $pdf = Pdf::loadView('pembimbing.kehadiran.pdf', compact(
            'presensiList', 'pembimbing', 'selectedPeserta', 'periode',
            'totalPresensi', 'totalTepat', 'totalTerlambat', 'totalIzin', 'totalSakit', 'totalAlpa'
        ));

        return $pdf->download($filename);
    }

    public function getDetail(Presensi $presensi)
    {
        $user = Auth::user();
        $pembimbing = $user->pembimbing;
        
        // Pastikan presensi ini milik peserta yang dibimbing
        if ($presensi->peserta->pembimbing_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        // Load relationships
        $presensi->load(['peserta', 'lokasi', 'jamKerja']);

        // Calculate duration
        $durasi_kerja = '-';
        if ($presensi->jam_masuk && $presensi->jam_keluar) {
            $jamMasuk = \Carbon\Carbon::parse($presensi->jam_masuk);
            $jamKeluar = \Carbon\Carbon::parse($presensi->jam_keluar);
            $durasi = $jamKeluar->diff($jamMasuk);
            $durasi_kerja = $durasi->h . ' jam ' . $durasi->i . ' menit';
        }

        // Get keterlambatan
        $keterlambatan = '-';
        if ($presensi->keterlambatan && $presensi->keterlambatan > 0) {
            $hours = floor($presensi->keterlambatan / 60);
            $minutes = $presensi->keterlambatan % 60;
            
            if ($hours > 0) {
                $keterlambatan = $hours . ' jam ' . $minutes . ' menit';
            } else {
                $keterlambatan = $minutes . ' menit';
            }
        } else {
            $keterlambatan = 'Tepat waktu';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'peserta_nama' => $presensi->peserta->nama_lengkap,
                'peserta_nim' => $presensi->peserta->nim,
                'tanggal' => \Carbon\Carbon::parse($presensi->tanggal)->format('d F Y'),
                'status' => $presensi->status,
                'jam_masuk' => $presensi->jam_masuk ? substr($presensi->jam_masuk, 0, 5) : '-',
                'jam_keluar' => $presensi->jam_keluar ? substr($presensi->jam_keluar, 0, 5) : '-',
                'lokasi' => $presensi->lokasi->nama_lokasi ?? '-',
                'alamat_lokasi' => $presensi->lokasi->alamat ?? '-',
                'catatan' => $presensi->catatan ?? 'Tidak ada catatan khusus untuk presensi ini.',
                'latitude_masuk' => $presensi->latitude_masuk ?? null,
                'longitude_masuk' => $presensi->longitude_masuk ?? null,
                'latitude_keluar' => $presensi->latitude_keluar ?? null,
                'longitude_keluar' => $presensi->longitude_keluar ?? null,
                'koordinat_masuk' => ($presensi->latitude_masuk && $presensi->longitude_masuk) ? $presensi->latitude_masuk . ', ' . $presensi->longitude_masuk : '-',
                'koordinat_keluar' => ($presensi->latitude_keluar && $presensi->longitude_keluar) ? $presensi->latitude_keluar . ', ' . $presensi->longitude_keluar : '-',
                'foto_masuk' => $presensi->foto_masuk ? asset('storage/' . $presensi->foto_masuk) : null,
                'foto_keluar' => $presensi->foto_keluar ? asset('storage/' . $presensi->foto_keluar) : null,
                'keterlambatan' => $keterlambatan,
                'durasi_kerja' => $durasi_kerja
            ]
        ]);
    }
}