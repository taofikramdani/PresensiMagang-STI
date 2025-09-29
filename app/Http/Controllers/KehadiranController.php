<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Presensi;
use App\Models\Peserta;
use App\Models\Pembimbing;
use App\Models\User;
use App\Models\JamKerja;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class KehadiranController extends Controller
{
    /**
     * Display a listing of attendance records
     */
    public function index(Request $request)
    {
        // Check if user is authenticated and is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        // Get filter parameters - Debugging tanggal issue
        $tanggal = $request->get('tanggal');
        $originalTanggal = $tanggal; // Store original for debugging
        
        if (!$tanggal || empty($tanggal)) {
            $tanggal = now()->format('Y-m-d');
        }
        
        $pembimbing_id = $request->get('pembimbing_id');
        $lokasi_id = $request->get('lokasi_id');
        $status = $request->get('status');
        $search = $request->get('search');
        
        // Debug what we got vs what we're using
        Log::info('Tanggal Debug', [
            'request_tanggal' => $originalTanggal,
            'final_tanggal' => $tanggal,
            'now' => now()->format('Y-m-d'),
            'url' => $request->fullUrl()
        ]);
        


        // Daily statistics
        $dailyStats = [
            'total_peserta' => Peserta::count(),
            'hadir' => Presensi::whereDate('tanggal', $tanggal)->whereIn('status', ['hadir', 'terlambat'])->count(),
            'izin' => Presensi::whereDate('tanggal', $tanggal)->whereIn('status', ['izin', 'sakit'])->count(),
            'alpa' => $this->calculateAlpaCount($tanggal),
            'is_working_day' => $this->isWorkingDay($tanggal),
            'active_schedules' => $this->getActiveWorkingSchedules()
        ];

        // Build query for attendance records
        $query = Presensi::with(['peserta.pembimbingDetail', 'lokasi'])
            ->whereDate('tanggal', $tanggal);

        // Apply filters
        if ($pembimbing_id) {
            $query->whereHas('peserta', function($q) use ($pembimbing_id) {
                $q->where('pembimbing_id', $pembimbing_id);
            });
        }

        if ($lokasi_id) {
            $query->where('lokasi_id', $lokasi_id);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('peserta', function($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%');
            });
        }

        $presensiRecords = $query->orderBy('jam_masuk', 'desc')->paginate(20);

        // Get peserta who haven't done attendance today (for alpa detection)
        // Only show alpa on working days and apply same filters
        $pesertaAlpa = collect();
        if ($this->isWorkingDay($tanggal)) {
            $pesertaHadir = Presensi::whereDate('tanggal', $tanggal);
                
            // Apply filters to get filtered pesertaHadir
            if ($pembimbing_id) {
                $pesertaHadir->whereHas('peserta', function($q) use ($pembimbing_id) {
                    $q->where('pembimbing_id', $pembimbing_id);
                });
            }

            if ($search) {
                $pesertaHadir->whereHas('peserta', function($q) use ($search) {
                    $q->where('nama_lengkap', 'like', '%' . $search . '%');
                });
            }
            
            $pesertaHadirIds = $pesertaHadir->pluck('peserta_id')->toArray();

            $alpaQuery = Peserta::whereNotIn('id', $pesertaHadirIds)
                ->where('tanggal_mulai', '<=', $tanggal)
                ->where('tanggal_selesai', '>=', $tanggal)
                ->with('pembimbingDetail');

            // Apply same filters to alpa detection
            if ($pembimbing_id) {
                $alpaQuery->where('pembimbing_id', $pembimbing_id);
            }

            if ($search) {
                $alpaQuery->where('nama_lengkap', 'like', '%' . $search . '%');
            }

            $pesertaAlpa = $alpaQuery->get();
        }

        // Get all pembimbing for filter dropdown
        $pembimbingList = Pembimbing::with('user')->orderBy('nama_lengkap')->get();
        
        // Get all lokasi for filter dropdown
        $lokasiList = \App\Models\Lokasi::orderBy('nama_lokasi')->get();
        
        // Get current user for layout
        $user = Auth::user();

        return view('kehadiran.index', compact(
            'presensiRecords',
            'pesertaAlpa',
            'dailyStats',
            'pembimbingList',
            'lokasiList',
            'tanggal',
            'pembimbing_id',
            'lokasi_id',
            'status',
            'search',
            'user'
        ));
    }

    /**
     * Show form for manual input
     */
    public function create()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        $pesertaList = Peserta::with(['pembimbingDetail', 'lokasi'])->orderBy('nama_lengkap')->get();
        return response()->json(['peserta' => $pesertaList]);
    }

    /**
     * Display the specified attendance record
     */
    public function show($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        $presensi = Presensi::with([
            'peserta.pembimbingDetail',
            'peserta.lokasi', 
            'jamKerja',
            'lokasi'
        ])->findOrFail($id);

        return view('kehadiran.detail', compact('presensi'));
    }

    /**
     * Store manual attendance record
     */
    public function store(Request $request)
    {
        try {
            if (!Auth::check() || Auth::user()->role !== 'admin') {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $validatedData = $request->validate([
                'peserta_id' => 'required|exists:peserta,id',
                'tanggal' => 'required|date',
                'jam_masuk' => 'required_if:status,hadir,terlambat',
                'jam_keluar' => 'nullable',
                'status' => 'required|in:hadir,terlambat,izin,sakit,alpa',
                'keterangan' => 'nullable|string|max:255'
            ]);

            // Check if attendance already exists
            $existing = Presensi::where('peserta_id', $request->peserta_id)
                ->whereDate('tanggal', $request->tanggal)
                ->first();

            if ($existing) {
                return response()->json(['error' => 'Presensi untuk peserta ini sudah ada pada tanggal tersebut'], 400);
            }

            // Get peserta data to use their lokasi_id
            $peserta = Peserta::find($validatedData['peserta_id']);
            if (!$peserta) {
                return response()->json(['error' => 'Data peserta tidak ditemukan'], 400);
            }

            // Get default jam kerja (active one)
            $jamKerja = \App\Models\JamKerja::where('is_active', true)->first();
            if (!$jamKerja) {
                return response()->json(['error' => 'Tidak ada jam kerja aktif yang tersedia'], 400);
            }

            // Use peserta's lokasi_id, or get default if peserta doesn't have one
            $lokasiId = $peserta->lokasi_id;
            if (!$lokasiId) {
                $lokasi = \App\Models\Lokasi::where('is_active', true)->first();
                if (!$lokasi) {
                    return response()->json(['error' => 'Tidak ada lokasi aktif yang tersedia'], 400);
                }
                $lokasiId = $lokasi->id;
            }

            $presensi = Presensi::create([
                'peserta_id' => $validatedData['peserta_id'],
                'jam_kerja_id' => $jamKerja->id,
                'lokasi_id' => $lokasiId,
                'tanggal' => $validatedData['tanggal'],
                'jam_masuk' => $validatedData['jam_masuk'] ?? null,
                'jam_keluar' => $validatedData['jam_keluar'] ?? null,
                'status' => $validatedData['status'],
                'keterangan' => $validatedData['keterangan'] ?? null
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil ditambahkan',
            'data' => $presensi->load('peserta.pembimbingDetail')
        ]);        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error storing presensi: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show form for editing attendance
     */
    public function edit(Presensi $presensi)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        return response()->json([
            'presensi' => $presensi->load('peserta.pembimbingDetail')
        ]);
    }

    /**
     * Update attendance record
     */
    public function update(Request $request, Presensi $presensi)
    {
        try {
            if (!Auth::check() || Auth::user()->role !== 'admin') {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $validatedData = $request->validate([
                'jam_masuk' => 'required_if:status,hadir,terlambat',
                'jam_keluar' => 'nullable',
                'status' => 'required|in:hadir,terlambat,izin,sakit,alpa',
                'keterangan' => 'nullable|string|max:255'
            ]);

            $presensi->update([
                'jam_masuk' => $validatedData['jam_masuk'] ?? null,
                'jam_keluar' => $validatedData['jam_keluar'] ?? null,
                'status' => $validatedData['status'],
                'keterangan' => $validatedData['keterangan'] ?? null
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil diperbarui',
            'data' => $presensi->load('peserta.pembimbingDetail')
        ]);        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating presensi: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export attendance data
     */
    public function export(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $format = $request->get('format', 'excel'); // excel or pdf

        $presensiData = Presensi::with(['peserta.pembimbing'])
            ->whereDate('tanggal', $tanggal)
            ->orderBy('jam_masuk')
            ->get();

        if ($format === 'excel') {
            return $this->exportToExcel($presensiData, $tanggal);
        } else {
            return $this->exportToPdf($presensiData, $tanggal);
        }
    }

    /**
     * Calculate alpa count for given date
     */
    private function calculateAlpaCount($tanggal)
    {
        // Only calculate alpa on working days
        if (!$this->isWorkingDay($tanggal)) {
            return 0;
        }

        $pesertaHadir = Presensi::whereDate('tanggal', $tanggal)
            ->pluck('peserta_id')
            ->toArray();

        return Peserta::whereNotIn('id', $pesertaHadir)
            ->where('tanggal_mulai', '<=', $tanggal)
            ->where('tanggal_selesai', '>=', $tanggal)
            ->count();
    }

    /**
     * Export to Excel (simplified - you can enhance with actual Excel library)
     */
    private function exportToExcel($data, $tanggal)
    {
        $filename = 'kehadiran_' . $tanggal . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['Nama Peserta', 'Pembimbing', 'Tanggal', 'Jam Masuk', 'Jam Keluar', 'Status', 'Keterangan']);
            
            // Data
            foreach ($data as $record) {
                fputcsv($file, [
                    $record->peserta->nama_lengkap,
                    $record->peserta->pembimbing->nama_lengkap ?? '-',
                    $record->tanggal,
                    $record->jam_masuk ?? '-',
                    $record->jam_keluar ?? '-',
                    ucfirst($record->status),
                    $record->keterangan ?? '-'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to PDF (placeholder - implement with actual PDF library)
     */
    private function exportToPdf($data, $tanggal)
    {
        // This is a placeholder - you would use a library like TCPDF or DOMPDF
        // For now, return the same CSV format
        return $this->exportToExcel($data, $tanggal);
    }

    /**
     * Export Excel with HTML template
     */
    public function exportExcel(Request $request)
    {
        // Get filter parameters
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $pembimbing_id = $request->get('pembimbing_id');
        $lokasi_id = $request->get('lokasi_id');
        $status = $request->get('status');
        $search = $request->get('search');

        // Build query for attendance records
        $query = Presensi::with(['peserta.pembimbingDetail', 'lokasi'])
            ->whereDate('tanggal', $tanggal);

        // Apply filters
        if ($pembimbing_id) {
            $query->whereHas('peserta', function($q) use ($pembimbing_id) {
                $q->where('pembimbing_id', $pembimbing_id);
            });
        }

        if ($lokasi_id) {
            $query->where('lokasi_id', $lokasi_id);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('peserta', function($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%');
            });
        }

        $presensiRecords = $query->orderBy('jam_masuk', 'desc')->get();

        // Get peserta who haven't done attendance today (for alpa detection)
        // Only check for alpa on working days and apply same filters
        $pesertaAlpa = collect();
        $isWorkingDay = $this->isWorkingDay($tanggal);
        
        if ($isWorkingDay) {
            $pesertaHadir = Presensi::whereDate('tanggal', $tanggal)
                ->pluck('peserta_id')
                ->toArray();

            $alpaQuery = Peserta::whereNotIn('id', $pesertaHadir)
                ->where('tanggal_mulai', '<=', $tanggal)
                ->where('tanggal_selesai', '>=', $tanggal)
                ->with('pembimbingDetail');

            // Apply same filters to alpa detection
            if ($pembimbing_id) {
                $alpaQuery->where('pembimbing_id', $pembimbing_id);
            }

            if ($search) {
                $alpaQuery->where('nama_lengkap', 'like', '%' . $search . '%');
            }

            $pesertaAlpa = $alpaQuery->get();
        }

        // Calculate filtered statistics  
        $filteredStats = $this->calculateFilteredStatistics($tanggal, $pembimbing_id, $status, $search);
        
        // Statistics
        $dailyStats = [
            'total_peserta' => $filteredStats['total_peserta'],
            'hadir' => $filteredStats['hadir'],
            'izin' => $filteredStats['izin'],
            'alpa' => $pesertaAlpa->count(),
            'is_working_day' => $isWorkingDay,
            'active_schedules' => $this->getActiveWorkingSchedules(),
            'filter_info' => $this->getFilterInfo($tanggal, $pembimbing_id, $status, $search)
        ];

        $response = response()->view('exports.kehadiran-excel', compact('presensiRecords', 'pesertaAlpa', 'tanggal', 'dailyStats'));
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment; filename="laporan_kehadiran_' . $tanggal . '.xls"');
        
        return $response;
    }

    /**
     * Export PDF with corporate template
     */
    public function exportPdf(Request $request)
    {
        // Get filter parameters
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $pembimbing_id = $request->get('pembimbing_id');
        $lokasi_id = $request->get('lokasi_id');
        $status = $request->get('status');
        $search = $request->get('search');

        // Build query for attendance records
        $query = Presensi::with(['peserta.pembimbingDetail', 'lokasi'])
            ->whereDate('tanggal', $tanggal);

        // Apply filters
        if ($pembimbing_id) {
            $query->whereHas('peserta', function($q) use ($pembimbing_id) {
                $q->where('pembimbing_id', $pembimbing_id);
            });
        }

        if ($lokasi_id) {
            $query->where('lokasi_id', $lokasi_id);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('peserta', function($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%');
            });
        }

        $presensiRecords = $query->orderBy('jam_masuk', 'desc')->get();

        // Get peserta who haven't done attendance today (for alpa detection)
        // Only check for alpa on working days and apply same filters
        $pesertaAlpa = collect();
        $isWorkingDay = $this->isWorkingDay($tanggal);
        
        if ($isWorkingDay) {
            $pesertaHadir = Presensi::whereDate('tanggal', $tanggal)
                ->pluck('peserta_id')
                ->toArray();

            $alpaQuery = Peserta::whereNotIn('id', $pesertaHadir)
                ->where('tanggal_mulai', '<=', $tanggal)
                ->where('tanggal_selesai', '>=', $tanggal)
                ->with('pembimbingDetail');

            // Apply same filters to alpa detection
            if ($pembimbing_id) {
                $alpaQuery->where('pembimbing_id', $pembimbing_id);
            }

            if ($search) {
                $alpaQuery->where('nama_lengkap', 'like', '%' . $search . '%');
            }

            $pesertaAlpa = $alpaQuery->get();
        }

        // Calculate filtered statistics
        $filteredStats = $this->calculateFilteredStatistics($tanggal, $pembimbing_id, $status, $search);
        
        // Statistics
        $dailyStats = [
            'total_peserta' => $filteredStats['total_peserta'],
            'hadir' => $filteredStats['hadir'],
            'izin' => $filteredStats['izin'],
            'alpa' => $pesertaAlpa->count(),
            'is_working_day' => $isWorkingDay,
            'active_schedules' => $this->getActiveWorkingSchedules(),
            'filter_info' => $this->getFilterInfo($tanggal, $pembimbing_id, $status, $search)
        ];

        $pdf = Pdf::loadView('exports.kehadiran-pdf', compact('presensiRecords', 'pesertaAlpa', 'tanggal', 'dailyStats'));
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('Laporan_Kehadiran_' . $tanggal . '.pdf');
    }

    /**
     * Check if given date is a working day based on JamKerja settings
     */
    private function isWorkingDay($tanggal)
    {
        $carbonDate = \Carbon\Carbon::parse($tanggal);
        $dayName = $carbonDate->locale('id')->dayName; // Get Indonesian day name
        
        // Check if there's any active jam kerja that includes this day
        $jamKerjaList = \App\Models\JamKerja::where('is_active', true)->get();
        
        if ($jamKerjaList->isEmpty()) {
            // If no active jam kerja found, default to Monday-Friday
            return !in_array($carbonDate->dayOfWeek, [0, 6]); // 0 = Sunday, 6 = Saturday
        }

        // Check if any of the active jam kerja includes this day
        foreach ($jamKerjaList as $jamKerja) {
            if ($jamKerja->isHariKerja($dayName)) {
                return true;
            }
        }

        // If no jam kerja includes this day, it's not a working day
        return false;
    }

    /**
     * Get active working schedules for debugging
     */
    private function getActiveWorkingSchedules()
    {
        return \App\Models\JamKerja::where('is_active', true)
            ->get()
            ->map(function($jamKerja) {
                return [
                    'nama_shift' => $jamKerja->nama_shift,
                    'hari_kerja' => $jamKerja->hari_kerja,
                    'jam_kerja' => $jamKerja->getJamKerjaDisplayName()
                ];
            });
    }

    /**
     * Calculate filtered statistics
     */
    private function calculateFilteredStatistics($tanggal, $pembimbing_id, $status, $search)
    {
        // Base query for peserta with filters
        $pesertaQuery = Peserta::where('tanggal_mulai', '<=', $tanggal)
            ->where('tanggal_selesai', '>=', $tanggal);

        if ($pembimbing_id) {
            $pesertaQuery->where('pembimbing_id', $pembimbing_id);
        }

        if ($search) {
            $pesertaQuery->where('nama_lengkap', 'like', '%' . $search . '%');
        }

        $totalPeserta = $pesertaQuery->count();

        // Base query for presensi with filters
        $presensiQuery = Presensi::whereDate('tanggal', $tanggal);

        if ($pembimbing_id) {
            $presensiQuery->whereHas('peserta', function($q) use ($pembimbing_id) {
                $q->where('pembimbing_id', $pembimbing_id);
            });
        }

        if ($search) {
            $presensiQuery->whereHas('peserta', function($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%');
            });
        }

        return [
            'total_peserta' => $totalPeserta,
            'hadir' => (clone $presensiQuery)->whereIn('status', ['hadir', 'terlambat'])->count(),
            'izin' => (clone $presensiQuery)->whereIn('status', ['izin', 'sakit'])->count()
        ];
    }

    /**
     * Get filter information for display
     */
    private function getFilterInfo($tanggal, $pembimbing_id, $status, $search)
    {
        $filters = [];
        
        if ($pembimbing_id) {
            $pembimbing = Pembimbing::where('user_id', $pembimbing_id)->first();
            $filters[] = 'Pembimbing: ' . ($pembimbing ? $pembimbing->nama_lengkap : 'Unknown');
        }
        
        if ($status) {
            $statusLabel = [
                'hadir' => 'Hadir',
                'terlambat' => 'Terlambat', 
                'izin' => 'Izin',
                'sakit' => 'Sakit',
                'alpa' => 'Alpa'
            ];
            $filters[] = 'Status: ' . ($statusLabel[$status] ?? $status);
        }
        
        if ($search) {
            $filters[] = 'Pencarian: ' . $search;
        }

        return [
            'has_filters' => count($filters) > 0,
            'filter_text' => count($filters) > 0 ? implode(', ', $filters) : 'Semua Data'
        ];
    }
}