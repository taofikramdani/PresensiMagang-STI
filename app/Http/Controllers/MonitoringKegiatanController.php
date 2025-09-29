<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kegiatan;
use App\Models\Peserta;
use App\Models\Pembimbing;
use App\Models\Lokasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class MonitoringKegiatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check if user is authenticated and is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        // Get filter parameters
        $tanggal_mulai = $request->get('tanggal_mulai', now()->subDays(7)->format('Y-m-d'));
        $tanggal_akhir = $request->get('tanggal_akhir', now()->format('Y-m-d'));
        $pembimbing_id = $request->get('pembimbing_id');
        $peserta_id = $request->get('peserta_id');
        $lokasi_id = $request->get('lokasi_id');

        // Build query for kegiatan
        $query = Kegiatan::with(['peserta.lokasi', 'pembimbing', 'pembimbingDetail'])
            ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);

        if ($pembimbing_id) {
            $query->whereHas('peserta', function($q) use ($pembimbing_id) {
                $q->where('pembimbing_id', $pembimbing_id);
            });
        }

        if ($peserta_id) {
            $query->where('peserta_id', $peserta_id);
        }

        if ($lokasi_id) {
            $query->whereHas('peserta', function($q) use ($lokasi_id) {
                $q->where('lokasi_id', $lokasi_id);
            });
        }

        // Get recent activities with pagination
        $recentActivities = $query->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calculate statistics
        $statistics = $this->calculateStatistics($tanggal_mulai, $tanggal_akhir, $pembimbing_id, $peserta_id, $lokasi_id);

        // Get chart data
        $dailyChartData = $this->getDailyChartData($tanggal_mulai, $tanggal_akhir, $pembimbing_id, $peserta_id, $lokasi_id);
        $categoryChartData = $this->getCategoryChartData($tanggal_mulai, $tanggal_akhir, $pembimbing_id, $peserta_id, $lokasi_id);

        // Get dropdown data
        $pembimbingList = Pembimbing::with('user')->where('status', 'aktif')->orderBy('nama_lengkap')->get();
        $pesertaList = Peserta::with('pembimbingDetail')->orderBy('nama_lengkap')->get();
        $lokasiList = Lokasi::where('is_active', true)->orderBy('nama_lokasi')->get();

        // Get current user for layout
        $user = Auth::user();

        return view('monitoring-kegiatan.index', compact(
            'recentActivities',
            'statistics',
            'dailyChartData',
            'categoryChartData',
            'pembimbingList',
            'pesertaList',
            'lokasiList',
            'tanggal_mulai',
            'tanggal_akhir',
            'pembimbing_id',
            'peserta_id',
            'lokasi_id',
            'user'
        ));
    }

    /**
     * Calculate statistics for the monitoring dashboard
     */
    private function calculateStatistics($tanggal_mulai, $tanggal_akhir, $pembimbing_id = null, $peserta_id = null, $lokasi_id = null)
    {
        $baseQuery = Kegiatan::whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);

        if ($pembimbing_id) {
            $baseQuery->whereHas('peserta', function($q) use ($pembimbing_id) {
                $q->where('pembimbing_id', $pembimbing_id);
            });
        }

        if ($peserta_id) {
            $baseQuery->where('peserta_id', $peserta_id);
        }

        if ($lokasi_id) {
            $baseQuery->whereHas('peserta', function($q) use ($lokasi_id) {
                $q->where('lokasi_id', $lokasi_id);
            });
        }

        $today = now()->format('Y-m-d');
        $startOfWeek = now()->startOfWeek()->format('Y-m-d');
        $endOfWeek = now()->endOfWeek()->format('Y-m-d');

        $totalKegiatan = (clone $baseQuery)->count();
        $daysBetween = Carbon::parse($tanggal_mulai)->diffInDays(Carbon::parse($tanggal_akhir)) + 1;
        $rataRataPerHari = $daysBetween > 0 ? round($totalKegiatan / $daysBetween, 2) : 0;

        return [
            'total_kegiatan' => $totalKegiatan,
            'kegiatan_hari_ini' => (clone $baseQuery)->whereDate('tanggal', $today)->count(),
            'kegiatan_minggu_ini' => (clone $baseQuery)->whereBetween('tanggal', [$startOfWeek, $endOfWeek])->count(),
            'peserta_aktif' => (clone $baseQuery)->distinct('peserta_id')->count('peserta_id'),
            'rata_rata_per_hari' => $rataRataPerHari
        ];
    }

    /**
     * Get daily chart data
     */
    private function getDailyChartData($tanggal_mulai, $tanggal_akhir, $pembimbing_id = null, $peserta_id = null, $lokasi_id = null)
    {
        $query = Kegiatan::selectRaw('DATE(tanggal) as date, COUNT(*) as count')
            ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);

        if ($pembimbing_id) {
            $query->whereHas('peserta', function($q) use ($pembimbing_id) {
                $q->where('pembimbing_id', $pembimbing_id);
            });
        }

        if ($peserta_id) {
            $query->where('peserta_id', $peserta_id);
        }

        if ($lokasi_id) {
            $query->whereHas('peserta', function($q) use ($lokasi_id) {
                $q->where('lokasi_id', $lokasi_id);
            });
        }

        $dailyData = $query->groupBy('date')
            ->orderBy('date')
            ->get();

        // Create labels and data arrays
        $labels = [];
        $data = [];
        
        $startDate = Carbon::parse($tanggal_mulai);
        $endDate = Carbon::parse($tanggal_akhir);
        
        while ($startDate <= $endDate) {
            $dateStr = $startDate->format('Y-m-d');
            $labels[] = $startDate->format('d/m');
            
            $dayData = $dailyData->firstWhere('date', $dateStr);
            $data[] = $dayData ? $dayData->count : 0;
            
            $startDate->addDay();
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get category chart data
     */
    private function getCategoryChartData($tanggal_mulai, $tanggal_akhir, $pembimbing_id = null, $peserta_id = null, $lokasi_id = null)
    {
        $query = Kegiatan::selectRaw('kategori_aktivitas, COUNT(*) as count')
            ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
            ->whereNotNull('kategori_aktivitas');

        if ($pembimbing_id) {
            $query->whereHas('peserta', function($q) use ($pembimbing_id) {
                $q->where('pembimbing_id', $pembimbing_id);
            });
        }

        if ($peserta_id) {
            $query->where('peserta_id', $peserta_id);
        }

        if ($lokasi_id) {
            $query->whereHas('peserta', function($q) use ($lokasi_id) {
                $q->where('lokasi_id', $lokasi_id);
            });
        }

        $categoryData = $query->groupBy('kategori_aktivitas')
            ->orderBy('count', 'desc')
            ->get();

        // Format kategori names
        $labels = $categoryData->map(function($item) {
            $categories = [
                'meeting' => 'Meeting',
                'pengerjaan_tugas' => 'Pengerjaan Tugas',
                'dokumentasi' => 'Dokumentasi',
                'laporan' => 'Laporan'
            ];
            return $categories[$item->kategori_aktivitas] ?? ucfirst($item->kategori_aktivitas);
        })->toArray();

        return [
            'labels' => $labels,
            'data' => $categoryData->pluck('count')->toArray()
        ];
    }

    /**
     * Export monitoring data
     */
    public function export(Request $request)
    {
        // Check if user is authenticated and is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        $format = $request->get('format', 'excel');
        $tanggal_mulai = $request->get('tanggal_mulai', now()->subDays(7)->format('Y-m-d'));
        $tanggal_akhir = $request->get('tanggal_akhir', now()->format('Y-m-d'));
        $pembimbing_id = $request->get('pembimbing_id');
        $peserta_id = $request->get('peserta_id');
        $lokasi_id = $request->get('lokasi_id');

        // Build query for export
        $query = Kegiatan::with(['peserta.lokasi', 'pembimbing', 'pembimbingDetail'])
            ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);

        if ($pembimbing_id) {
            $query->whereHas('peserta', function($q) use ($pembimbing_id) {
                $q->where('pembimbing_id', $pembimbing_id);
            });
        }

        if ($peserta_id) {
            $query->where('peserta_id', $peserta_id);
        }

        if ($lokasi_id) {
            $query->whereHas('peserta', function($q) use ($lokasi_id) {
                $q->where('lokasi_id', $lokasi_id);
            });
        }

        $kegiatan = $query->orderBy('tanggal', 'desc')->get();

        if ($format === 'excel') {
            return $this->exportToExcel($kegiatan, $tanggal_mulai, $tanggal_akhir);
        } elseif ($format === 'pdf') {
            return $this->exportToPdf($kegiatan, $tanggal_mulai, $tanggal_akhir);
        }

        return redirect()->back()->with('error', 'Format export tidak valid');
    }

    /**
     * Export to Excel
     */
    private function exportToExcel($kegiatan, $tanggal_mulai, $tanggal_akhir)
    {
        $filename = 'monitoring_kegiatan_' . $tanggal_mulai . '_to_' . $tanggal_akhir . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($kegiatan) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fputs($file, "\xEF\xBB\xBF");
            
            // CSV Headers
            fputcsv($file, [
                'Tanggal',
                'Jam Mulai',
                'Jam Selesai',
                'Peserta',
                'NIM',
                'Lokasi',
                'Pembimbing',
                'Judul Kegiatan',
                'Kategori Aktivitas',
                'Deskripsi',
                'Bukti',
                'Created At'
            ]);

            // CSV Data
            foreach ($kegiatan as $item) {
                // Get pembimbing name using the correct field names
                $pembimbingName = '';
                if ($item->pembimbingDetail && $item->pembimbingDetail->nama_lengkap) {
                    $pembimbingName = $item->pembimbingDetail->nama_lengkap;
                } elseif ($item->pembimbing && $item->pembimbing->name) {
                    $pembimbingName = $item->pembimbing->name;
                } elseif ($item->peserta && $item->peserta->pembimbingDetail) {
                    $pembimbingName = $item->peserta->pembimbingDetail->nama_lengkap;
                } elseif ($item->peserta && $item->peserta->pembimbing) {
                    $pembimbingName = $item->peserta->pembimbing->name;
                } else {
                    $pembimbingName = 'Belum ditentukan';
                }

                fputcsv($file, [
                    $item->tanggal,
                    $item->jam_mulai,
                    $item->jam_selesai,
                    $item->peserta->nama_lengkap,
                    $item->peserta->nim,
                    $item->peserta->lokasi ? $item->peserta->lokasi->nama_lokasi : '-',
                    $pembimbingName,
                    $item->judul,
                    $item->kategori_aktivitas,
                    $item->deskripsi,
                    $item->bukti,
                    $item->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to PDF (placeholder for future implementation)
     */
    private function exportToPdf($kegiatan, $tanggal_mulai, $tanggal_akhir)
    {
        // For now, return a simple message
        // In the future, you can implement PDF generation using packages like DOMPDF or TCPDF
        return response()->json([
            'message' => 'PDF export will be implemented soon',
            'data_count' => $kegiatan->count()
        ]);
    }

    /**
     * Export kegiatan data to Excel
     */
    public function exportExcel(Request $request)
    {
        // Get filter parameters
        $tanggal_mulai = $request->get('tanggal_mulai', now()->subDays(7)->format('Y-m-d'));
        $tanggal_akhir = $request->get('tanggal_akhir', now()->format('Y-m-d'));
        $pembimbing_id = $request->get('pembimbing_id');
        $peserta_id = $request->get('peserta_id');
        $lokasi_id = $request->get('lokasi_id');

        // Build query for kegiatan
        $query = Kegiatan::with(['peserta.lokasi', 'peserta.pembimbingDetail', 'pembimbing', 'pembimbingDetail'])
            ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);

        if ($pembimbing_id) {
            $query->whereHas('peserta', function($q) use ($pembimbing_id) {
                $q->where('pembimbing_id', $pembimbing_id);
            });
        }

        if ($peserta_id) {
            $query->where('peserta_id', $peserta_id);
        }

        if ($lokasi_id) {
            $query->whereHas('peserta', function($q) use ($lokasi_id) {
                $q->where('lokasi_id', $lokasi_id);
            });
        }

        $kegiatanRecords = $query->orderBy('tanggal', 'desc')->get();

        // Debug: Load relasi lokasi secara eksplisit jika belum ter-load
        $kegiatanRecords->load('peserta.lokasi');

        // Calculate statistics
        $statistics = $this->calculateStatistics($tanggal_mulai, $tanggal_akhir, $pembimbing_id, $peserta_id, $lokasi_id);

        // Filter information for display
        $filterInfo = $this->getFilterInfo($tanggal_mulai, $tanggal_akhir, $pembimbing_id, $peserta_id, $lokasi_id);

        $response = response()->view('exports.monitoring-kegiatan-excel', compact(
            'kegiatanRecords', 
            'statistics', 
            'tanggal_mulai', 
            'tanggal_akhir', 
            'filterInfo'
        ));
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment; filename="monitoring_kegiatan_' . $tanggal_mulai . '_' . $tanggal_akhir . '.xls"');
        
        return $response;
    }

    /**
     * Export kegiatan data to PDF
     */
    public function exportPdf(Request $request)
    {
        // Get filter parameters
        $tanggal_mulai = $request->get('tanggal_mulai', now()->subDays(7)->format('Y-m-d'));
        $tanggal_akhir = $request->get('tanggal_akhir', now()->format('Y-m-d'));
        $pembimbing_id = $request->get('pembimbing_id');
        $peserta_id = $request->get('peserta_id');
        $lokasi_id = $request->get('lokasi_id');

        // Build query for kegiatan
        $query = Kegiatan::with(['peserta.lokasi', 'peserta.pembimbingDetail', 'pembimbing', 'pembimbingDetail'])
            ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);

        if ($pembimbing_id) {
            $query->whereHas('peserta', function($q) use ($pembimbing_id) {
                $q->where('pembimbing_id', $pembimbing_id);
            });
        }

        if ($peserta_id) {
            $query->where('peserta_id', $peserta_id);
        }

        if ($lokasi_id) {
            $query->whereHas('peserta', function($q) use ($lokasi_id) {
                $q->where('lokasi_id', $lokasi_id);
            });
        }

        $kegiatanRecords = $query->orderBy('tanggal', 'desc')->get();

        // Debug: Load relasi lokasi secara eksplisit jika belum ter-load
        $kegiatanRecords->load('peserta.lokasi');

        // Calculate statistics
        $statistics = $this->calculateStatistics($tanggal_mulai, $tanggal_akhir, $pembimbing_id, $peserta_id, $lokasi_id);

        // Filter information for display
        $filterInfo = $this->getFilterInfo($tanggal_mulai, $tanggal_akhir, $pembimbing_id, $peserta_id, $lokasi_id);

        $pdf = PDF::loadView('exports.monitoring-kegiatan-pdf', compact(
            'kegiatanRecords', 
            'statistics', 
            'tanggal_mulai', 
            'tanggal_akhir', 
            'filterInfo'
        ));

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('monitoring_kegiatan_' . $tanggal_mulai . '_' . $tanggal_akhir . '.pdf');
    }

    /**
     * Get filter information for display in exports
     */
    private function getFilterInfo($tanggal_mulai, $tanggal_akhir, $pembimbing_id = null, $peserta_id = null, $lokasi_id = null)
    {
        $filterInfo = [];
        
        $filterInfo['Periode'] = Carbon::parse($tanggal_mulai)->format('d/m/Y') . ' - ' . Carbon::parse($tanggal_akhir)->format('d/m/Y');
        
        if ($pembimbing_id) {
            $pembimbing = User::find($pembimbing_id);
            $filterInfo['Pembimbing'] = $pembimbing ? $pembimbing->name : 'Tidak Ditemukan';
        } else {
            $filterInfo['Pembimbing'] = 'Semua Pembimbing';
        }
        
        if ($peserta_id) {
            $peserta = Peserta::find($peserta_id);
            $filterInfo['Peserta'] = $peserta ? $peserta->nama_lengkap : 'Tidak Ditemukan';
        } else {
            $filterInfo['Peserta'] = 'Semua Peserta';
        }
        
        if ($lokasi_id) {
            $lokasi = Lokasi::find($lokasi_id);
            $filterInfo['Lokasi'] = $lokasi ? $lokasi->nama_lokasi : 'Tidak Ditemukan';
        } else {
            $filterInfo['Lokasi'] = 'Semua Lokasi';
        }
        
        return $filterInfo;
    }
}