<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Peserta;
use App\Models\Perizinan;
use App\Models\Presensi;
use App\Models\JamKerja;
use App\Models\Lokasi;
use App\Models\Notification;
use Carbon\Carbon;

class IzinController extends Controller
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
        
        // Base query untuk perizinan
        $pesertaIds = $pesertaList->pluck('id');
        $query = Perizinan::with(['peserta', 'pembimbing'])
            ->whereIn('peserta_id', $pesertaIds);
        
        // Apply filters
        if ($request->filled('peserta')) {
            $query->where('peserta_id', $request->peserta);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }
        
        if ($request->filled('bulan')) {
            $bulan = $request->bulan;
            $query->whereYear('tanggal', substr($bulan, 0, 4))
                  ->whereMonth('tanggal', substr($bulan, 5, 2));
        }
        
        $perizinanList = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Statistics
        $totalPerizinan = Perizinan::whereIn('peserta_id', $pesertaIds)->count();
        $totalPending = Perizinan::whereIn('peserta_id', $pesertaIds)->where('status', 'pending')->count();
        $totalDisetujui = Perizinan::whereIn('peserta_id', $pesertaIds)->where('status', 'disetujui')->count();
        $totalDitolak = Perizinan::whereIn('peserta_id', $pesertaIds)->where('status', 'ditolak')->count();
        
        // Pending hari ini atau yang urgent
        $pendingToday = Perizinan::with('peserta')
            ->whereIn('peserta_id', $pesertaIds)
            ->where('status', 'pending')
            ->whereDate('tanggal', '>=', now()->format('Y-m-d'))
            ->orderBy('tanggal', 'asc')
            ->limit(6)
            ->get();
        
        // Set izin pending untuk dashboard
        $izinPending = $totalPending;
        
        return view('pembimbing.izin.index', compact(
            'pesertaList',
            'perizinanList',
            'pendingToday',
            'totalPerizinan',
            'totalPending',
            'totalDisetujui',
            'totalDitolak',
            'izinPending'
        ));
    }
    
    public function show(Perizinan $perizinan)
    {
        $user = Auth::user();
        $pembimbing = $user->pembimbing;
        
        // Pastikan perizinan ini milik peserta yang dibimbing
        if ($perizinan->peserta->pembimbing_id !== $user->id) {
            abort(403, 'Unauthorized access');
        }
        
        return view('pembimbing.izin.show', compact('perizinan'));
    }
    
    public function approve(Request $request, Perizinan $perizinan)
    {
        $user = Auth::user();
        $pembimbing = $user->pembimbing;
        
        // Pastikan perizinan ini milik peserta yang dibimbing
        if ($perizinan->peserta->pembimbing_id !== $user->id) {
            abort(403, 'Unauthorized access');
        }
        
        // Validate that perizinan is still pending
        if ($perizinan->status !== 'pending') {
            return back()->with('error', 'Perizinan sudah diproses sebelumnya');
        }
        
        $perizinan->update([
            'status' => 'disetujui',
            'catatan_pembimbing' => $request->catatan_pembimbing,
            'tanggal_approval' => now(),
            'approved_by' => $pembimbing->id
        ]);

        // Create notification for peserta
        Notification::createApprovalIzin(
            $perizinan->peserta->user_id,
            $perizinan,
            'disetujui'
        );

        // Setelah perizinan disetujui, otomatis buat/update presensi
        $this->createOrUpdatePresensiFromPerizinan($perizinan);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Perizinan berhasil disetujui dan status presensi telah diperbarui'
            ]);
        }

        return back()->with('success', 'Perizinan berhasil disetujui dan status presensi telah diperbarui');
    }
    
    public function reject(Request $request, Perizinan $perizinan)
    {
        $user = Auth::user();
        $pembimbing = $user->pembimbing;
        
        // Pastikan perizinan ini milik peserta yang dibimbing
        if ($perizinan->peserta->pembimbing_id !== $user->id) {
            abort(403, 'Unauthorized access');
        }
        
        // Validate that perizinan is still pending
        if ($perizinan->status !== 'pending') {
            return back()->with('error', 'Perizinan sudah diproses sebelumnya');
        }
        
        $perizinan->update([
            'status' => 'ditolak',
            'catatan_pembimbing' => $request->catatan_pembimbing,
            'tanggal_approval' => now(),
            'approved_by' => $pembimbing->id
        ]);
        
        // Create notification for peserta
        Notification::createApprovalIzin(
            $perizinan->peserta->user_id,
            $perizinan,
            'ditolak'
        );
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Perizinan berhasil ditolak'
            ]);
        }
        
        return back()->with('success', 'Perizinan berhasil ditolak');
    }

    /**
     * Create or update presensi when perizinan is approved
     */
    private function createOrUpdatePresensiFromPerizinan(Perizinan $perizinan)
    {
        try {
            Log::info('=== START createOrUpdatePresensiFromPerizinan (IzinController) ===', [
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

            Log::info('=== END createOrUpdatePresensiFromPerizinan SUCCESS (IzinController) ===');

        } catch (\Exception $e) {
            Log::error('=== ERROR createOrUpdatePresensiFromPerizinan (IzinController) ===', [
                'perizinan_id' => $perizinan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}