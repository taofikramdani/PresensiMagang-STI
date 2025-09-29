<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $peserta = Peserta::where('user_id', $user->id)->first();
        
        if (!$peserta) {
            return redirect()->route('peserta.dashboard')->with('error', 'Data peserta tidak ditemukan.');
        }

        $query = Kegiatan::byPeserta($peserta->id);

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('month')) {
            $query->byMonth($request->month);
        }

        if ($request->filled('kategori')) {
            $query->byKategori($request->kategori);
        }

        $kegiatans = $query->orderBy('tanggal', 'asc')->orderBy('jam_mulai', 'asc')->paginate(15);

        return view('peserta.kegiatan.index', compact('kegiatans', 'peserta'));
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $peserta = Peserta::where('user_id', $user->id)->first();
            
            if (!$peserta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peserta tidak ditemukan.'
                ], 404);
            }

            $request->validate([
                'tanggal' => 'required|date',
                'jam_mulai' => 'required',
                'jam_selesai' => 'nullable',
                'judul' => 'required|string|max:255',
                'deskripsi' => 'required|string',
                'kategori_aktivitas' => 'required|in:meeting,pengerjaan_tugas,dokumentasi,laporan',
                'bukti' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx|max:5120' // 5MB
            ]);

            $buktiPath = null;
            if ($request->hasFile('bukti')) {
                $file = $request->file('bukti');
                $fileName = time() . '_' . $peserta->id . '_' . $file->getClientOriginalName();
                $buktiPath = $file->storeAs('kegiatan/bukti', $fileName, 'public');
            }

            $kegiatan = Kegiatan::create([
                'peserta_id' => $peserta->id,
                'tanggal' => $request->tanggal,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'kategori_aktivitas' => $request->kategori_aktivitas,
                'bukti' => $buktiPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kegiatan berhasil disimpan.',
                'data' => $kegiatan->load('peserta')
            ]);

        } catch (\Exception $e) {
            Log::error('Error storing kegiatan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan kegiatan.'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = Auth::user();
            $peserta = Peserta::where('user_id', $user->id)->first();
            
            if (!$peserta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peserta tidak ditemukan.'
                ], 404);
            }

            $kegiatan = Kegiatan::byPeserta($peserta->id)
                ->where('id', $id)
                ->first();

            if (!$kegiatan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kegiatan tidak ditemukan.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $kegiatan->id,
                    'tanggal' => $kegiatan->formatted_tanggal,
                    'jam_mulai' => $kegiatan->formatted_jam_mulai,
                    'jam_selesai' => $kegiatan->formatted_jam_selesai,
                    'judul' => $kegiatan->judul,
                    'deskripsi' => $kegiatan->deskripsi,
                    'bukti' => $kegiatan->bukti_file_name,
                    'bukti_url' => $kegiatan->bukti ? Storage::url($kegiatan->bukti) : null,
                    'is_image' => $kegiatan->is_bukti_image
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing kegiatan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data kegiatan.'
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $user = Auth::user();
            $peserta = Peserta::where('user_id', $user->id)->first();
            
            if (!$peserta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peserta tidak ditemukan.'
                ], 404);
            }

            $kegiatan = Kegiatan::byPeserta($peserta->id)
                ->where('id', $id)
                ->first();

            if (!$kegiatan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kegiatan tidak ditemukan.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $kegiatan->id,
                    'tanggal' => Carbon::parse($kegiatan->tanggal)->format('Y-m-d'),
                    'jam_mulai' => $kegiatan->formatted_jam_mulai,
                    'jam_selesai' => $kegiatan->formatted_jam_selesai,
                    'judul' => $kegiatan->judul,
                    'deskripsi' => $kegiatan->deskripsi,
                    'kategori_aktivitas' => $kegiatan->kategori_aktivitas,
                    'bukti' => $kegiatan->bukti_file_name
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error editing kegiatan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data kegiatan.'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $peserta = Peserta::where('user_id', $user->id)->first();
            
            if (!$peserta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peserta tidak ditemukan.'
                ], 404);
            }

            $kegiatan = Kegiatan::byPeserta($peserta->id)
                ->where('id', $id)
                ->first();

            if (!$kegiatan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kegiatan tidak ditemukan.'
                ], 404);
            }

            $request->validate([
                'tanggal' => 'required|date',
                'jam_mulai' => 'required',
                'jam_selesai' => 'nullable',
                'judul' => 'required|string|max:255',
                'deskripsi' => 'required|string',
                'kategori_aktivitas' => 'required|in:meeting,pengerjaan_tugas,dokumentasi,laporan',
                'bukti' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx|max:5120' // 5MB
            ]);

            $buktiPath = $kegiatan->bukti;
            
            if ($request->hasFile('bukti')) {
                // Delete old file if exists
                if ($buktiPath && Storage::disk('public')->exists($buktiPath)) {
                    Storage::disk('public')->delete($buktiPath);
                }
                
                $file = $request->file('bukti');
                $fileName = time() . '_' . $peserta->id . '_' . $file->getClientOriginalName();
                $buktiPath = $file->storeAs('kegiatan/bukti', $fileName, 'public');
            }

            $kegiatan->update([
                'tanggal' => $request->tanggal,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'kategori_aktivitas' => $request->kategori_aktivitas,
                'bukti' => $buktiPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kegiatan berhasil diperbarui.',
                'data' => $kegiatan->fresh()->load('peserta')
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating kegiatan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui kegiatan.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $peserta = Peserta::where('user_id', $user->id)->first();
            
            if (!$peserta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peserta tidak ditemukan.'
                ], 404);
            }

            $kegiatan = Kegiatan::byPeserta($peserta->id)
                ->where('id', $id)
                ->first();

            if (!$kegiatan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kegiatan tidak ditemukan.'
                ], 404);
            }

            // Delete file if exists
            if ($kegiatan->bukti && Storage::disk('public')->exists($kegiatan->bukti)) {
                Storage::disk('public')->delete($kegiatan->bukti);
            }

            $kegiatan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kegiatan berhasil dihapus.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting kegiatan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus kegiatan.'
            ], 500);
        }
    }

    public function search(Request $request)
    {
        try {
            $user = Auth::user();
            $peserta = Peserta::where('user_id', $user->id)->first();
            
            if (!$peserta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peserta tidak ditemukan.'
                ], 404);
            }

            $query = Kegiatan::byPeserta($peserta->id);

            if ($request->filled('search')) {
                $query->search($request->search);
            }

            if ($request->filled('month')) {
                $query->byMonth($request->month);
            }

            if ($request->filled('kategori')) {
                $query->byKategori($request->kategori);
            }

            $kegiatans = $query->orderBy('tanggal', 'asc')->orderBy('jam_mulai', 'asc')->get();

            return response()->json([
                'success' => true,
                'data' => $kegiatans->map(function ($kegiatan) {
                    return [
                        'id' => $kegiatan->id,
                        'tanggal' => $kegiatan->tanggal,
                        'jam_mulai' => $kegiatan->formatted_jam_mulai,
                        'jam_selesai' => $kegiatan->formatted_jam_selesai,
                        'duration' => $kegiatan->duration,
                        'judul' => $kegiatan->judul,
                        'kategori_aktivitas' => $kegiatan->formatted_kategori_aktivitas,
                        'deskripsi' => substr($kegiatan->deskripsi, 0, 100) . (strlen($kegiatan->deskripsi) > 100 ? '...' : ''),
                        'bukti' => $kegiatan->bukti_file_name,
                        'bukti_type' => $kegiatan->bukti_file_type
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching kegiatan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari kegiatan.'
            ], 500);
        }
    }

    public function export()
    {
        try {
            $user = Auth::user();
            $peserta = Peserta::where('user_id', $user->id)->first();
            
            if (!$peserta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peserta tidak ditemukan.'
                ], 404);
            }

            $kegiatans = Kegiatan::byPeserta($peserta->id)
                ->latest()
                ->get();

            // For now, return JSON data. Can be extended to Excel export later
            return response()->json([
                'success' => true,
                'message' => 'Data kegiatan berhasil diekspor.',
                'data' => $kegiatans->map(function ($kegiatan) {
                    return [
                        'Tanggal' => Carbon::parse($kegiatan->tanggal)->format('d/m/Y'),
                        'Jam' => $kegiatan->formatted_jam,
                        'Judul Kegiatan' => $kegiatan->judul,
                        'Deskripsi' => $kegiatan->deskripsi,
                        'Bukti' => $kegiatan->bukti_file_name ?? 'Tidak ada'
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting kegiatan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengekspor data kegiatan.'
            ], 500);
        }
    }

    public function exportPdf()
    {
        try {
            $user = Auth::user();
            $peserta = Peserta::where('user_id', $user->id)->first();
            
            if (!$peserta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peserta tidak ditemukan.'
                ], 404);
            }

            $kegiatans = Kegiatan::byPeserta($peserta->id)
                ->orderBy('tanggal', 'asc')
                ->orderBy('jam_mulai', 'asc')
                ->get();

            $peserta->load('lokasi');

            // Generate PDF
            $pdf = Pdf::loadView('peserta.kegiatan.pdf', [
                'kegiatans' => $kegiatans,
                'peserta' => $peserta,
                'startDate' => $kegiatans->first()?->tanggal ? Carbon::parse($kegiatans->first()->tanggal) : Carbon::now(),
                'endDate' => $kegiatans->last()?->tanggal ? Carbon::parse($kegiatans->last()->tanggal) : Carbon::now(),
                'periode' => 'Laporan Kegiatan Harian'
            ]);

            $fileName = 'laporan-kegiatan-harian-' . $peserta->nama_lengkap . '-' . Carbon::now()->format('Y-m-d') . '.pdf';
            
            return $pdf->download($fileName);

        } catch (\Exception $e) {
            Log::error('Error exporting kegiatan PDF: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengekspor PDF kegiatan.'
            ], 500);
        }
    }
}