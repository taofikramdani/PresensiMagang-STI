<?php

namespace App\Http\Controllers;

use App\Models\Perizinan;
use App\Models\Peserta;
use App\Models\Pembimbing;
use App\Models\Presensi;
use App\Models\JamKerja;
use App\Models\Lokasi;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PerizinanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Cek apakah user adalah peserta
        $peserta = Auth::user()->peserta;
        if (!$peserta) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat perizinan.');
        }

        $query = Perizinan::with(['peserta', 'pembimbing'])
            ->where('peserta_id', $peserta->id);
        
        // Filter berdasarkan status jika ada
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        // Filter berdasarkan jenis jika ada
        if ($request->has('jenis') && $request->jenis != '') {
            $query->where('jenis', $request->jenis);
        }
        
        // Filter berdasarkan bulan jika ada
        if ($request->has('bulan') && $request->bulan != '') {
            $query->whereMonth('tanggal', Carbon::parse($request->bulan)->month)
                  ->whereYear('tanggal', Carbon::parse($request->bulan)->year);
        }
        
        $perizinans = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('peserta.izin.index', compact('perizinans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('perizinan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Debug request data
        Log::info('Store request received', [
            'has_file' => $request->hasFile('bukti_dokumen'),
            'all_files' => $request->allFiles(),
            'request_data' => $request->all()
        ]);

        $request->validate([
            'jenis' => 'required|in:izin,sakit',
            'tanggal' => 'required|date|after_or_equal:today',
            'keterangan' => 'required|string|min:10',
            'bukti_dokumen' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'jenis.required' => 'Jenis perizinan harus dipilih',
            'jenis.in' => 'Jenis perizinan tidak valid',
            'tanggal.required' => 'Tanggal harus diisi',
            'tanggal.date' => 'Format tanggal tidak valid',
            'tanggal.after_or_equal' => 'Tanggal tidak boleh kurang dari hari ini',
            'keterangan.required' => 'Keterangan harus diisi',
            'keterangan.min' => 'Keterangan minimal 10 karakter',
            'bukti_dokumen.file' => 'Bukti dokumen harus berupa file',
            'bukti_dokumen.mimes' => 'Bukti dokumen harus berformat PDF, JPG, JPEG, atau PNG',
            'bukti_dokumen.max' => 'Ukuran bukti dokumen maksimal 2MB',
        ]);

        // Cek apakah user adalah peserta
        $peserta = Auth::user()->peserta;
        if (!$peserta) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk membuat perizinan.');
        }

        // Cek apakah sudah ada perizinan di tanggal yang sama
        $existingPerizinan = Perizinan::where('peserta_id', $peserta->id)
            ->where('tanggal', $request->tanggal)
            ->first();

        if ($existingPerizinan) {
            return redirect()->back()->with('error', 'Anda sudah mengajukan perizinan pada tanggal tersebut.');
        }

        // Handle upload bukti dokumen
        $buktiDokumen = null;
        if ($request->hasFile('bukti_dokumen')) {
            $file = $request->file('bukti_dokumen');
            $fileName = time() . '_' . $peserta->id . '_' . $file->getClientOriginalName();
            
            // Debug file info
            Log::info('File upload attempt:', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'temp_path' => $file->getRealPath(),
                'target_filename' => $fileName
            ]);
            
            // Try multiple storage methods
            try {
                // Method 1: Using Storage facade
                $storedPath = Storage::disk('public')->putFileAs('perizinan', $file, $fileName);
                Log::info('Storage facade result: ' . ($storedPath ?: 'FAILED'));
                
                if (!$storedPath) {
                    // Method 2: Using file move
                    $targetPath = storage_path('app/public/perizinan/' . $fileName);
                    $moved = $file->move(dirname($targetPath), basename($targetPath));
                    Log::info('File move result: ' . ($moved ? 'SUCCESS' : 'FAILED'));
                    
                    if ($moved) {
                        $storedPath = 'perizinan/' . $fileName;
                    }
                }
                
                if ($storedPath) {
                    $buktiDokumen = $fileName;
                    
                    // Verify file exists
                    $fullPath = storage_path('app/public/perizinan/' . $fileName);
                    Log::info('File verification:', [
                        'stored_path' => $storedPath,
                        'full_path' => $fullPath,
                        'file_exists' => file_exists($fullPath),
                        'file_size' => file_exists($fullPath) ? filesize($fullPath) : 'N/A'
                    ]);
                } else {
                    Log::error('All storage methods failed for file: ' . $fileName);
                    return redirect()->back()->with('error', 'Gagal mengupload bukti dokumen.');
                }
                
            } catch (\Exception $e) {
                Log::error('File upload exception: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Terjadi kesalahan saat mengupload file.');
            }
        }

        $perizinan = Perizinan::create([
            'peserta_id' => $peserta->id,
            'jenis' => $request->jenis,
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'bukti_dokumen' => $buktiDokumen,
            'status' => 'pending'
        ]);

        // Create notification for pembimbing
        if ($peserta->pembimbing && $peserta->pembimbing->user_id) {
            Notification::createPengajuanIzin(
                $peserta->pembimbing->user_id,
                $peserta,
                $perizinan
            );
        }

        return redirect()->route('peserta.izin.index')->with('success', 'Perizinan berhasil diajukan dan menunggu persetujuan pembimbing.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Perizinan $perizinan)
    {
        $perizinan->load(['peserta', 'pembimbing']);
        return view('perizinan.show', compact('perizinan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Perizinan $perizinan)
    {
        // Hanya bisa edit jika status masih pending
        if ($perizinan->status != 'pending') {
            return redirect()->route('peserta.izin.index')->with('error', 'Perizinan yang sudah diproses tidak dapat diedit.');
        }

        // Hanya pemilik perizinan yang bisa edit
        $peserta = Auth::user()->peserta;
        if (!$peserta || $perizinan->peserta_id != $peserta->id) {
            return redirect()->route('peserta.izin.index')->with('error', 'Anda tidak memiliki akses untuk mengedit perizinan ini.');
        }

        return view('perizinan.edit', compact('perizinan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Perizinan $perizinan)
    {
        // Hanya bisa update jika status masih pending
        if ($perizinan->status != 'pending') {
            return redirect()->route('peserta.izin.index')->with('error', 'Perizinan yang sudah diproses tidak dapat diedit.');
        }

        $request->validate([
            'jenis' => 'required|in:izin,sakit',
            'tanggal' => 'required|date|after_or_equal:today',
            'keterangan' => 'required|string|min:10',
        ], [
            'jenis.required' => 'Jenis perizinan harus dipilih',
            'jenis.in' => 'Jenis perizinan tidak valid',
            'tanggal.required' => 'Tanggal harus diisi',
            'tanggal.date' => 'Format tanggal tidak valid',
            'tanggal.after_or_equal' => 'Tanggal tidak boleh kurang dari hari ini',
            'keterangan.required' => 'Keterangan harus diisi',
            'keterangan.min' => 'Keterangan minimal 10 karakter',
        ]);

        // Cek apakah sudah ada perizinan di tanggal yang sama (kecuali perizinan ini sendiri)
        $existingPerizinan = Perizinan::where('peserta_id', $perizinan->peserta_id)
            ->where('tanggal', $request->tanggal)
            ->where('id', '!=', $perizinan->id)
            ->first();

        if ($existingPerizinan) {
            return redirect()->back()->with('error', 'Anda sudah mengajukan perizinan pada tanggal tersebut.');
        }

        $perizinan->update([
            'jenis' => $request->jenis,
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('peserta.izin.index')->with('success', 'Perizinan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Perizinan $perizinan)
    {
        // Hanya bisa hapus jika status masih pending
        if ($perizinan->status != 'pending') {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Perizinan yang sudah diproses tidak dapat dihapus.'], 400);
            }
            return redirect()->route('peserta.izin.index')->with('error', 'Perizinan yang sudah diproses tidak dapat dihapus.');
        }

        // Hanya pemilik perizinan yang bisa hapus
        $peserta = Auth::user()->peserta;
        if (!$peserta || $perizinan->peserta_id != $peserta->id) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Anda tidak memiliki akses untuk menghapus perizinan ini.'], 403);
            }
            return redirect()->route('peserta.izin.index')->with('error', 'Anda tidak memiliki akses untuk menghapus perizinan ini.');
        }

        $perizinan->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Perizinan berhasil dihapus.'], 200);
        }
        return redirect()->route('peserta.izin.index')->with('success', 'Perizinan berhasil dihapus.');
    }

    /**
     * Approve perizinan (untuk pembimbing)
     */
    public function approve(Request $request, Perizinan $perizinan)
    {
        // Cek apakah user adalah pembimbing
        $pembimbing = Auth::user()->pembimbing;
        if (!$pembimbing) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menyetujui perizinan.');
        }

        $request->validate([
            'catatan_pembimbing' => 'nullable|string|max:500',
        ]);

        Log::info('Approve perizinan started', [
            'perizinan_id' => $perizinan->id,
            'jenis' => $perizinan->jenis,
            'tanggal' => $perizinan->tanggal,
            'peserta_id' => $perizinan->peserta_id
        ]);

        $perizinan->setujui($pembimbing->id, $request->catatan_pembimbing);

        Log::info('Perizinan setujui completed, calling createOrUpdatePresensiFromPerizinan');

        // Setelah perizinan disetujui, otomatis buat/update presensi
        $this->createOrUpdatePresensiFromPerizinan($perizinan);

        Log::info('createOrUpdatePresensiFromPerizinan completed');

        return redirect()->back()->with('success', 'Perizinan berhasil disetujui dan status presensi telah diperbarui.');
    }

    /**
     * Reject perizinan (untuk pembimbing)
     */
    public function reject(Request $request, Perizinan $perizinan)
    {
        // Cek apakah user adalah pembimbing
        $pembimbing = Auth::user()->pembimbing;
        if (!$pembimbing) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menolak perizinan.');
        }

        $request->validate([
            'catatan_pembimbing' => 'required|string|min:10|max:500',
        ], [
            'catatan_pembimbing.required' => 'Catatan penolakan harus diisi',
            'catatan_pembimbing.min' => 'Catatan penolakan minimal 10 karakter',
            'catatan_pembimbing.max' => 'Catatan penolakan maksimal 500 karakter',
        ]);

        $perizinan->tolak($pembimbing->id, $request->catatan_pembimbing);

        return redirect()->back()->with('success', 'Perizinan berhasil ditolak.');
    }

    /**
     * Dashboard untuk pembimbing melihat perizinan yang perlu disetujui
     */
    public function pembimbingDashboard()
    {
        $pembimbing = Auth::user()->pembimbing;
        if (!$pembimbing) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses sebagai pembimbing.');
        }

        $perizinansPending = Perizinan::pending()
            ->with('peserta')
            ->orderBy('created_at', 'desc')
            ->get();

        $statistik = [
            'total_pending' => Perizinan::pending()->count(),
            'total_disetujui_bulan_ini' => Perizinan::disetujui()
                ->whereMonth('tanggal_approval', Carbon::now()->month)
                ->count(),
            'total_ditolak_bulan_ini' => Perizinan::ditolak()
                ->whereMonth('tanggal_approval', Carbon::now()->month)
                ->count(),
        ];

        return view('perizinan.pembimbing-dashboard', compact('perizinansPending', 'statistik'));
    }

    /**
     * Create or update presensi when perizinan is approved
     */
    private function createOrUpdatePresensiFromPerizinan(Perizinan $perizinan)
    {
        try {
            Log::info('=== START createOrUpdatePresensiFromPerizinan ===', [
                'perizinan_id' => $perizinan->id,
                'peserta_id' => $perizinan->peserta_id,
                'tanggal' => $perizinan->tanggal,
                'jenis' => $perizinan->jenis,
                'status' => $perizinan->status
            ]);

            // Cek apakah sudah ada presensi di tanggal izin
            $presensi = Presensi::where('peserta_id', $perizinan->peserta_id)
                ->whereDate('tanggal', $perizinan->tanggal)
                ->first();

            Log::info('Presensi existing check', [
                'presensi_found' => $presensi ? true : false,
                'presensi_id' => $presensi ? $presensi->id : null,
                'presensi_status' => $presensi ? $presensi->status : null
            ]);

            // Get jam kerja aktif untuk tanggal izin
            $tanggalIzin = Carbon::parse($perizinan->tanggal);
            $hariMap = [
                'monday' => 'senin',
                'tuesday' => 'selasa',
                'wednesday' => 'rabu',
                'thursday' => 'kamis',
                'friday' => 'jumat',
                'saturday' => 'sabtu',
                'sunday' => 'minggu'
            ];
            
            $hariInggris = strtolower($tanggalIzin->format('l'));
            $hariIzin = $hariMap[$hariInggris] ?? $hariInggris; // Convert to Indonesian format
            
            $jamKerja = JamKerja::where('is_active', true)
                ->get()
                ->first(function ($jk) use ($hariIzin) {
                    $hariKerja = $jk->hari_kerja ?? [];
                    return in_array($hariIzin, $hariKerja);
                });

            // Fallback ke jam kerja aktif pertama
            if (!$jamKerja) {
                $jamKerja = JamKerja::where('is_active', true)->first();
            }

            // Get lokasi aktif
            $lokasi = Lokasi::where('is_active', true)->first();

            // Pastikan ada jam kerja dan lokasi (required untuk database)
            if (!$jamKerja) {
                Log::error('Tidak ada jam kerja aktif ditemukan');
                return;
            }

            if (!$lokasi) {
                Log::error('Tidak ada lokasi aktif ditemukan');
                return;
            }

            Log::info('Jam kerja dan lokasi check', [
                'jam_kerja_found' => $jamKerja ? true : false,
                'jam_kerja_id' => $jamKerja ? $jamKerja->id : null,
                'lokasi_found' => $lokasi ? true : false,
                'lokasi_id' => $lokasi ? $lokasi->id : null,
                'hari_izin' => $hariIzin
            ]);

            if (!$presensi) {
                // Jika belum ada presensi, buat baru
                $newPresensi = Presensi::create([
                    'peserta_id' => $perizinan->peserta_id,
                    'tanggal' => $perizinan->tanggal,
                    'jam_kerja_id' => $jamKerja->id, // required field
                    'lokasi_id' => $lokasi->id, // required field
                    'status' => $perizinan->jenis, // 'izin' atau 'sakit'
                    'catatan' => 'Auto dari perizinan: ' . $perizinan->keterangan,
                    'keterlambatan' => 0,
                    'durasi_kerja' => 0,
                ]);

                Log::info('NEW Presensi otomatis dibuat dari perizinan', [
                    'new_presensi_id' => $newPresensi->id,
                    'perizinan_id' => $perizinan->id,
                    'peserta_id' => $perizinan->peserta_id,
                    'tanggal' => $perizinan->tanggal,
                    'status' => $perizinan->jenis
                ]);

            } else {
                // Jika sudah ada presensi, update status jika belum izin/sakit
                if (!in_array($presensi->status, ['izin', 'sakit'])) {
                    $presensi->update([
                        'status' => $perizinan->jenis,
                        'catatan' => 'Update dari perizinan: ' . $perizinan->keterangan,
                        'keterlambatan' => 0,
                    ]);

                    Log::info('UPDATE Presensi diupdate dari perizinan', [
                        'presensi_id' => $presensi->id,
                        'perizinan_id' => $perizinan->id,
                        'status_lama' => $presensi->getOriginal('status'),
                        'status_baru' => $perizinan->jenis
                    ]);
                } else {
                    Log::info('SKIP Presensi sudah memiliki status izin/sakit', [
                        'presensi_id' => $presensi->id,
                        'status_existing' => $presensi->status
                    ]);
                }
            }

            Log::info('=== END createOrUpdatePresensiFromPerizinan SUCCESS ===');

        } catch (\Exception $e) {
            Log::error('=== ERROR createOrUpdatePresensiFromPerizinan ===', [
                'perizinan_id' => $perizinan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}