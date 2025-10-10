<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\PengajuanPresensi;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class PengajuanPresensiController extends Controller
{
    /**
     * Menyimpan pengajuan presensi baru
     */
    public function store(Request $request)
    {
        $peserta = Peserta::where('user_id', Auth::id())->first();
        
        if (!$peserta) {
            return redirect()->back()->withErrors(['error' => 'Data peserta tidak ditemukan']);
        }

        // Validasi input
        $request->validate([
            'jenis_pengajuan' => ['required', Rule::in(['lupa_checkout', 'presensi_keliru'])],
            'tanggal_presensi' => [
                'required',
                'date',
                'before_or_equal:' . Carbon::yesterday()->toDateString(),
                'after_or_equal:' . Carbon::now()->subDays(3)->toDateString(),
            ],
            'jam_masuk' => [
                'nullable',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->jenis_pengajuan !== 'lupa_checkout' && empty($value)) {
                        $fail('Jam masuk wajib diisi untuk jenis pengajuan ini.');
                    }
                }
            ],
            'jam_keluar' => 'required|date_format:H:i',
            'penjelasan' => 'required|string|min:20|max:1000',
        ], [
            'tanggal_presensi.before_or_equal' => 'Tanggal presensi tidak boleh hari ini atau yang akan datang.',
            'tanggal_presensi.after_or_equal' => 'Tanggal presensi maksimal 3 hari yang lalu.',
            'penjelasan.min' => 'Penjelasan minimal 20 karakter.',
            'jam_masuk.date_format' => 'Format jam masuk tidak valid (HH:MM).',
            'jam_keluar.date_format' => 'Format jam keluar tidak valid (HH:MM).',
        ]);

        // Cek apakah sudah ada pengajuan untuk tanggal yang sama
        $existing = PengajuanPresensi::where('peserta_id', $peserta->id)
            ->where('tanggal_presensi', $request->tanggal_presensi)
            ->where('status', '!=', 'ditolak')
            ->first();

        if ($existing) {
            return redirect()->back()->withErrors([
                'tanggal_presensi' => 'Sudah ada pengajuan untuk tanggal ini.'
            ]);
        }

        // Validasi jam masuk dan keluar
        if ($request->jam_masuk && $request->jam_keluar) {
            $jamMasuk = Carbon::createFromFormat('H:i', $request->jam_masuk);
            $jamKeluar = Carbon::createFromFormat('H:i', $request->jam_keluar);
            
            if ($jamMasuk->greaterThanOrEqualTo($jamKeluar)) {
                return redirect()->back()->withErrors([
                    'jam_keluar' => 'Jam keluar harus lebih besar dari jam masuk.'
                ]);
            }
        }

        // Simpan pengajuan
        PengajuanPresensi::create([
            'peserta_id' => $peserta->id,
            'tanggal_presensi' => $request->tanggal_presensi,
            'jenis_pengajuan' => $request->jenis_pengajuan,
            'jam_masuk' => $request->jam_masuk,
            'jam_keluar' => $request->jam_keluar,
            'penjelasan' => $request->penjelasan,
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success_presensi', 'Pengajuan presensi berhasil dikirim dan menunggu persetujuan pembimbing.');
    }

    /**
     * Menampilkan data pengajuan untuk AJAX
     */
    public function getData(Request $request)
    {
        $peserta = Peserta::where('user_id', Auth::id())->first();
        
        if (!$peserta) {
            return response()->json(['error' => 'Data peserta tidak ditemukan'], 404);
        }

        $pengajuans = PengajuanPresensi::where('peserta_id', $peserta->id)
            ->with('approver')
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(function ($pengajuan) {
                $pengajuan->append(['jenis_pengajuan_display', 'status_display', 'status_color']);
            });

        $statistics = [
            'pending' => $pengajuans->where('status', 'pending')->count(),
            'disetujui' => $pengajuans->where('status', 'disetujui')->count(),
            'ditolak' => $pengajuans->where('status', 'ditolak')->count(),
        ];

        return response()->json([
            'pengajuans' => $pengajuans,
            'statistics' => $statistics
        ]);
    }

    /**
     * Menampilkan detail pengajuan
     */
    public function show($id)
    {
        $peserta = Peserta::where('user_id', Auth::id())->first();
        
        $pengajuan = PengajuanPresensi::where('peserta_id', $peserta->id)
            ->with(['approver', 'peserta'])
            ->findOrFail($id);

        return response()->json($pengajuan);
    }

    /**
     * Menghapus pengajuan (hanya yang pending)
     */
    public function destroy($id)
    {
        $peserta = Peserta::where('user_id', Auth::id())->first();
        
        $pengajuan = PengajuanPresensi::where('peserta_id', $peserta->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        $pengajuan->delete();

        // Check if request expects JSON (AJAX request)
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan presensi berhasil dihapus.'
            ]);
        }

        return redirect()->back()->with('success_presensi', 'Pengajuan presensi berhasil dihapus.');
    }

    /**
     * Mendapatkan tanggal yang valid untuk pengajuan
     */
    public function getValidDates()
    {
        $dates = [];
        for ($i = 1; $i <= 3; $i++) {
            $date = Carbon::now()->subDays($i);
            $dates[] = [
                'value' => $date->toDateString(),
                'label' => $date->format('d M Y') . ' (' . $date->locale('id')->dayName . ')'
            ];
        }

        return response()->json($dates);
    }
}
