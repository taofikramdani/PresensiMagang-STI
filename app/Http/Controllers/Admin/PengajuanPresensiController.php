<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanPresensi;
use App\Models\Presensi;
use App\Models\JamKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengajuanPresensiController extends Controller
{
    /**
     * Menampilkan daftar pengajuan presensi
     */
    public function index(Request $request)
    {
        $query = PengajuanPresensi::with(['peserta.user', 'peserta.pembimbingDetail', 'approver'])
                    ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_presensi', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_presensi', '<=', $request->tanggal_sampai);
        }

        // Search by peserta name
        if ($request->filled('search')) {
            $query->whereHas('peserta', function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->search . '%');
            });
        }

        $pengajuans = $query->paginate(15);

        // Statistics
        $statistics = [
            'total' => PengajuanPresensi::count(),
            'pending' => PengajuanPresensi::where('status', 'pending')->count(),
            'disetujui' => PengajuanPresensi::where('status', 'disetujui')->count(),
            'ditolak' => PengajuanPresensi::where('status', 'ditolak')->count(),
        ];

        return view('admin.pengajuan-presensi.index', compact('pengajuans', 'statistics'));
    }

    /**
     * Menampilkan detail pengajuan
     */
    public function show($id)
    {
        $pengajuan = PengajuanPresensi::with(['peserta.user', 'peserta.pembimbingDetail', 'approver'])
                                    ->findOrFail($id);

        return response()->json($pengajuan);
    }

    /**
     * Approve pengajuan presensi dan buat data presensi
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'nullable|string|max:500'
        ]);

        try {
            $pengajuan = PengajuanPresensi::with('peserta')->findOrFail($id);
            
            // Pastikan masih pending
            if ($pengajuan->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan sudah diproses sebelumnya'
                ]);
            }

            DB::beginTransaction();

            // Update status pengajuan
            $pengajuan->update([
                'status' => 'disetujui',
                'keterangan_pembimbing' => $request->keterangan,
                'approved_at' => now(),
                'approved_by' => Auth::id()
            ]);

            // Cari atau buat data presensi
            $presensi = Presensi::updateOrCreate(
                [
                    'peserta_id' => $pengajuan->peserta_id,
                    'tanggal' => $pengajuan->tanggal_presensi
                ],
                [
                    'jam_masuk' => $pengajuan->jam_masuk,
                    'jam_keluar' => $pengajuan->jam_keluar,
                    'status' => $this->determineStatus($pengajuan),
                    'keterangan_masuk' => 'Presensi dari pengajuan yang disetujui: ' . $pengajuan->jenis_pengajuan_display,
                    'keterangan_keluar' => $pengajuan->jam_keluar ? 'Checkout dari pengajuan yang disetujui' : null,
                    'durasi_kerja' => $this->hitungDurasiKerja($pengajuan->jam_masuk, $pengajuan->jam_keluar),
                    'lokasi_id' => $pengajuan->peserta->lokasi_id,
                    'jam_kerja_id' => JamKerja::where('is_active', true)->first()?->id,
                    'keterangan' => $request->keterangan ?: 'Pengajuan disetujui oleh admin',
                    'manual_entry' => true,
                    'pengajuan_presensi_id' => $pengajuan->id
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan presensi berhasil disetujui dan data presensi telah dibuat',
                'data' => [
                    'pengajuan' => $pengajuan->fresh(['peserta.user', 'approver']),
                    'presensi' => $presensi
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tolak pengajuan presensi
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'required|string|min:10|max:500'
        ], [
            'keterangan.required' => 'Alasan penolakan wajib diisi',
            'keterangan.min' => 'Alasan penolakan minimal 10 karakter'
        ]);

        $pengajuan = PengajuanPresensi::findOrFail($id);
        
        if ($pengajuan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan sudah diproses sebelumnya'
            ]);
        }

        $pengajuan->update([
            'status' => 'ditolak',
            'keterangan_pembimbing' => $request->keterangan,
            'approved_at' => now(),
            'approved_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan presensi berhasil ditolak',
            'data' => $pengajuan->fresh(['peserta.user', 'approver'])
        ]);
    }

    /**
     * Determine status based on pengajuan type and time
     */
    private function determineStatus($pengajuan)
    {
        if (!$pengajuan->jam_masuk) {
            return 'hadir'; // Default for lupa checkout
        }

        // Get active work schedule
        $jamKerja = JamKerja::where('is_active', true)->first();
        
        if (!$jamKerja) {
            return 'hadir';
        }

        $jamMasuk = Carbon::parse($pengajuan->jam_masuk);
        $jamKerjaMulai = Carbon::parse($jamKerja->jam_masuk);
        
        // Add tolerance (e.g., 15 minutes)
        $batasTerlambat = $jamKerjaMulai->addMinutes(15);
        
        return $jamMasuk->greaterThan($batasTerlambat) ? 'terlambat' : 'hadir';
    }

    /**
     * Calculate work duration in minutes
     */
    private function hitungDurasiKerja($jamMasuk, $jamKeluar)
    {
        if (!$jamMasuk || !$jamKeluar) {
            return 0;
        }
        
        $masuk = Carbon::parse($jamMasuk);
        $keluar = Carbon::parse($jamKeluar);
        
        return $masuk->diffInMinutes($keluar);
    }
}