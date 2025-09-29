<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\Pembimbing;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanKegiatanController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $pembimbing = Pembimbing::where('user_id', $user->id)->first();
            
            if (!$pembimbing) {
                return redirect()->route('pembimbing.dashboard')->with('error', 'Data pembimbing tidak ditemukan.');
            }

            // Get peserta yang dibimbing
            $pesertaQuery = Peserta::where('pembimbing_id', $user->id);

            // Apply search filter
            if ($request->filled('search')) {
                $pesertaQuery->whereHas('user', function($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%')
                          ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }

            $pesertaList = $pesertaQuery->with(['user', 'kegiatans' => function($query) use ($request) {
                // Apply date filters to kegiatan
                if ($request->filled('tanggal_mulai')) {
                    $query->where('tanggal', '>=', $request->tanggal_mulai);
                }
                if ($request->filled('tanggal_selesai')) {
                    $query->where('tanggal', '<=', $request->tanggal_selesai);
                }
                if ($request->filled('kategori')) {
                    $query->where('kategori_aktivitas', $request->kategori);
                }
                $query->orderBy('tanggal', 'desc')->orderBy('jam_mulai', 'desc');
            }])->paginate(10);

            // Get summary statistics
            $totalPeserta = $pesertaList->total();
            $totalKegiatan = 0;
            $kegiatanHariIni = 0;
            $today = Carbon::today();

            foreach ($pesertaList as $peserta) {
                $totalKegiatan += $peserta->kegiatans->count();
                $kegiatanHariIni += $peserta->kegiatans->where('tanggal', $today->format('Y-m-d'))->count();
            }

            return view('pembimbing.laporan-kegiatan.index', compact(
                'pesertaList',
                'totalPeserta',
                'totalKegiatan',
                'kegiatanHariIni',
                'pembimbing'
            ));

        } catch (\Exception $e) {
            Log::error('Error in laporan kegiatan index: ' . $e->getMessage());
            return redirect()->route('pembimbing.dashboard')->with('error', 'Terjadi kesalahan saat memuat laporan kegiatan.');
        }
    }

    public function show($pesertaId, Request $request)
    {
        try {
            $user = Auth::user();
            $pembimbing = Pembimbing::where('user_id', $user->id)->first();
            
            if (!$pembimbing) {
                return redirect()->route('pembimbing.dashboard')->with('error', 'Data pembimbing tidak ditemukan.');
            }

            // Verify peserta belongs to this pembimbing
            $peserta = Peserta::where('id', $pesertaId)
                              ->where('pembimbing_id', $user->id)
                              ->with('user')
                              ->first();

            if (!$peserta) {
                return redirect()->route('pembimbing.laporan-kegiatan.index')
                               ->with('error', 'Data peserta tidak ditemukan atau bukan bimbingan Anda.');
            }

            // Get kegiatan with filters
            $kegiatanQuery = Kegiatan::where('peserta_id', $peserta->id);

            if ($request->filled('tanggal_mulai')) {
                $kegiatanQuery->where('tanggal', '>=', $request->tanggal_mulai);
            }
            if ($request->filled('tanggal_selesai')) {
                $kegiatanQuery->where('tanggal', '<=', $request->tanggal_selesai);
            }
            if ($request->filled('kategori')) {
                $kegiatanQuery->where('kategori_aktivitas', $request->kategori);
            }
            if ($request->filled('search')) {
                $kegiatanQuery->where(function($query) use ($request) {
                    $query->where('judul', 'like', '%' . $request->search . '%')
                          ->orWhere('deskripsi', 'like', '%' . $request->search . '%');
                });
            }

            $kegiatans = $kegiatanQuery->orderBy('tanggal', 'asc')->orderBy('jam_mulai', 'asc')->get();

            $kegiatanList = $kegiatanQuery->orderBy('tanggal', 'desc')
                                          ->orderBy('jam_mulai', 'desc')
                                          ->paginate(15);

            // Statistics
            $totalKegiatan = $kegiatanQuery->count();
            $kegiatanBulanIni = $kegiatanQuery->whereMonth('tanggal', Carbon::now()->month)
                                              ->whereYear('tanggal', Carbon::now()->year)
                                              ->count();

            // Group by category for chart
            $kegiatanByCategory = $kegiatanQuery->selectRaw('kategori_aktivitas, COUNT(*) as total')
                                                ->groupBy('kategori_aktivitas')
                                                ->pluck('total', 'kategori_aktivitas');

            return view('pembimbing.laporan-kegiatan.show', compact(
                'peserta',
                'kegiatanList',
                'totalKegiatan',
                'kegiatanBulanIni',
                'kegiatanByCategory',
                'pembimbing'
            ));

        } catch (\Exception $e) {
            Log::error('Error in laporan kegiatan show: ' . $e->getMessage());
            return redirect()->route('pembimbing.laporan-kegiatan.index')
                           ->with('error', 'Terjadi kesalahan saat memuat detail laporan.');
        }
    }
    
    public function export($pesertaId, Request $request)
    {
        try {
            $user = Auth::user();
            $pembimbing = Pembimbing::where('user_id', $user->id)->first();
            
            if (!$pembimbing) {
                return response()->json(['error' => 'Data pembimbing tidak ditemukan.'], 404);
            }

            $peserta = Peserta::where('id', $pesertaId)
                              ->where('pembimbing_id', $user->id)
                              ->with('user')
                              ->first();

            if (!$peserta) {
                return response()->json(['error' => 'Data peserta tidak ditemukan.'], 404);
            }

            $kegiatanQuery = Kegiatan::where('peserta_id', $peserta->id);

            // Apply filters
            if ($request->filled('tanggal_mulai')) {
                $kegiatanQuery->where('tanggal', '>=', $request->tanggal_mulai);
            }
            if ($request->filled('tanggal_selesai')) {
                $kegiatanQuery->where('tanggal', '<=', $request->tanggal_selesai);
            }

            $kegiatanList = $kegiatanQuery->orderBy('tanggal', 'asc')
                                          ->orderBy('jam_mulai', 'asc')
                                          ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'peserta' => [
                        'nama' => $peserta->user->name,
                        'email' => $peserta->user->email,
                        'nim' => $peserta->nim ?? '-',
                        'instansi' => $peserta->instansi ?? '-'
                    ],
                    'kegiatan' => $kegiatanList->map(function($kegiatan) {
                        return [
                            'tanggal' => Carbon::parse($kegiatan->tanggal)->format('d/m/Y'),
                            'jam_mulai' => $kegiatan->formatted_jam_mulai,
                            'jam_selesai' => $kegiatan->formatted_jam_selesai ?? '-',
                            'durasi' => $kegiatan->duration ?? '-',
                            'judul' => $kegiatan->judul,
                            'kategori' => $kegiatan->formatted_kategori_aktivitas,
                            'deskripsi' => $kegiatan->deskripsi,
                            'bukti' => $kegiatan->bukti ? 'Ya' : 'Tidak'
                        ];
                    }),
                    'total_kegiatan' => $kegiatanList->count(),
                    'periode' => [
                        'mulai' => $request->tanggal_mulai ?? $kegiatanList->min('tanggal'),
                        'selesai' => $request->tanggal_selesai ?? $kegiatanList->max('tanggal')
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in export laporan kegiatan: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat export data.'], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $user = Auth::user();
            $pembimbing = Pembimbing::where('user_id', $user->id)->first();
            
            if (!$pembimbing) {
                return redirect()->route('pembimbing.dashboard')->with('error', 'Data pembimbing tidak ditemukan.');
            }

            // Build query untuk kegiatan
            $query = Kegiatan::with(['peserta.user'])
                ->whereHas('peserta', function($q) use ($user) {
                    $q->where('pembimbing_id', $user->id);
                });

            // Apply filters
            if ($request->filled('search')) {
                $query->whereHas('peserta.user', function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->filled('peserta_id')) {
                $query->where('peserta_id', $request->peserta_id);
            }

            if ($request->filled('tanggal_mulai')) {
                $query->where('tanggal', '>=', $request->tanggal_mulai);
            }

            if ($request->filled('tanggal_selesai')) {
                $query->where('tanggal', '<=', $request->tanggal_selesai);
            }

            if ($request->filled('kategori')) {
                $query->where('kategori_aktivitas', $request->kategori);
            }

            $kegiatanList = $query->orderBy('tanggal', 'desc')
                                 ->orderBy('jam_mulai', 'desc')
                                 ->get();

            // Get filter info for filename and report
            $selectedPeserta = null;
            if ($request->filled('peserta_id')) {
                $selectedPeserta = Peserta::find($request->peserta_id);
            }

            $periode = '';
            if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
                $periode = Carbon::parse($request->tanggal_mulai)->format('d/m/Y') . ' - ' . 
                           Carbon::parse($request->tanggal_selesai)->format('d/m/Y');
            } elseif ($request->filled('tanggal_mulai')) {
                $periode = 'Sejak ' . Carbon::parse($request->tanggal_mulai)->format('d/m/Y');
            } elseif ($request->filled('tanggal_selesai')) {
                $periode = 'Sampai ' . Carbon::parse($request->tanggal_selesai)->format('d/m/Y');
            } else {
                $periode = 'Semua Periode';
            }

            $kategoriFilter = $request->kategori ? ucfirst(str_replace('_', ' ', $request->kategori)) : 'Semua Kategori';

            $filename = 'laporan-kegiatan-' . 
                       ($selectedPeserta ? $selectedPeserta->nama_lengkap : 'semua-peserta') . '-' . 
                       now()->format('Y-m-d') . '.xls';

            return response()->view('pembimbing.laporan-kegiatan.excel', compact(
                'kegiatanList', 'pembimbing', 'selectedPeserta', 'periode', 'kategoriFilter'
            ))->header('Content-Type', 'application/vnd.ms-excel')
              ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            Log::error('Error in export Excel laporan kegiatan: ' . $e->getMessage());
            return redirect()->route('pembimbing.laporan-kegiatan.index')->with('error', 'Terjadi kesalahan saat export Excel.');
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $user = Auth::user();
            $pembimbing = Pembimbing::where('user_id', $user->id)->first();
            
            if (!$pembimbing) {
                return redirect()->route('pembimbing.dashboard')->with('error', 'Data pembimbing tidak ditemukan.');
            }

            // Build query untuk kegiatan
            $query = Kegiatan::with(['peserta.user'])
                ->whereHas('peserta', function($q) use ($user) {
                    $q->where('pembimbing_id', $user->id);
                });

            // Apply filters
            if ($request->filled('search')) {
                $query->whereHas('peserta.user', function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->filled('peserta_id')) {
                $query->where('peserta_id', $request->peserta_id);
            }

            if ($request->filled('tanggal_mulai')) {
                $query->where('tanggal', '>=', $request->tanggal_mulai);
            }

            if ($request->filled('tanggal_selesai')) {
                $query->where('tanggal', '<=', $request->tanggal_selesai);
            }

            if ($request->filled('kategori')) {
                $query->where('kategori_aktivitas', $request->kategori);
            }

            $kegiatanList = $query->orderBy('tanggal', 'asc')
                                 ->orderBy('jam_mulai', 'asc')
                                 ->get();

            // Get filter info for filename and report
            $selectedPeserta = null;
            if ($request->filled('peserta_id')) {
                $selectedPeserta = Peserta::find($request->peserta_id);
            }

            $periode = '';
            if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
                $periode = Carbon::parse($request->tanggal_mulai)->format('d/m/Y') . ' - ' . 
                           Carbon::parse($request->tanggal_selesai)->format('d/m/Y');
            } elseif ($request->filled('tanggal_mulai')) {
                $periode = 'Sejak ' . Carbon::parse($request->tanggal_mulai)->format('d/m/Y');
            } elseif ($request->filled('tanggal_selesai')) {
                $periode = 'Sampai ' . Carbon::parse($request->tanggal_selesai)->format('d/m/Y');
            } else {
                $periode = 'Semua Periode';
            }

            $kategoriFilter = $request->kategori ? ucfirst(str_replace('_', ' ', $request->kategori)) : 'Semua Kategori';

            $filename = 'laporan-kegiatan-' . 
                       ($selectedPeserta ? $selectedPeserta->nama_lengkap : 'semua-peserta') . '-' . 
                       now()->format('Y-m-d') . '.pdf';

            // Alias untuk kompatibilitas template
            $peserta = $selectedPeserta;
            
            $pdf = Pdf::loadView('pembimbing.laporan-kegiatan.pdf', compact(
                'kegiatanList', 'pembimbing', 'selectedPeserta', 'peserta', 'periode', 'kategoriFilter'
            ));

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Error in export PDF laporan kegiatan: ' . $e->getMessage());
            return redirect()->route('pembimbing.laporan-kegiatan.index')->with('error', 'Terjadi kesalahan saat export PDF.');
        }
    }
}