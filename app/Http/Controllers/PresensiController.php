<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\Peserta;
use App\Models\JamKerja;
use App\Models\Lokasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PresensiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $peserta = Peserta::where('user_id', $user->id)->first();
        
        if (!$peserta) {
            return redirect()->route('dashboard')->with('error', 'Data peserta tidak ditemukan.');
        }

        // Get presensi hari ini
        $presensiHariIni = Presensi::where('peserta_id', $peserta->id)
            ->whereDate('tanggal', today())
            ->first();

        // Get jam kerja aktif
        $jamKerja = JamKerja::aktif()->first();
        
        // Get lokasi aktif
        $lokasi = Lokasi::aktif()->first();

        return view('peserta.presensi.index', compact('presensiHariIni', 'jamKerja', 'lokasi', 'peserta'));
    }

    public function absenMasuk(Request $request)
    {
        $user = Auth::user();
        $peserta = Peserta::where('user_id', $user->id)->first();
        
        if (!$peserta) {
            return response()->json(['success' => false, 'message' => 'Data peserta tidak ditemukan.']);
        }

        // Validasi sudah absen hari ini
        $presensiHariIni = Presensi::where('peserta_id', $peserta->id)
            ->whereDate('tanggal', today())
            ->first();

        if ($presensiHariIni && $presensiHariIni->jam_masuk) {
            return response()->json(['success' => false, 'message' => 'Anda sudah melakukan presensi masuk hari ini.']);
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'keterangan' => 'nullable|string|max:255'
        ]);

        try {
            // Get jam kerja dan lokasi aktif
            $jamKerja = JamKerja::aktif()->first();
            $lokasi = Lokasi::aktif()->first();

            if (!$jamKerja || !$lokasi) {
                return response()->json(['success' => false, 'message' => 'Jam kerja atau lokasi belum dikonfigurasi.']);
            }

            // Validasi hari kerja
            $hariMap = [
                'monday' => 'senin',
                'tuesday' => 'selasa',
                'wednesday' => 'rabu',
                'thursday' => 'kamis',
                'friday' => 'jumat',
                'saturday' => 'sabtu',
                'sunday' => 'minggu'
            ];
            
            $hariInggris = strtolower(Carbon::now('Asia/Jakarta')->format('l'));
            $hariIni = $hariMap[$hariInggris] ?? $hariInggris; // Convert to Indonesian format
            $hariKerja = $jamKerja->hari_kerja ?? [];
            
            if (!in_array($hariIni, $hariKerja)) {
                $hariKerjaText = implode(', ', $hariKerja);
                return response()->json([
                    'success' => false, 
                    'message' => "Hari ini ($hariIni) bukan hari kerja. Hari kerja: $hariKerjaText"
                ]);
            }

            // Validasi lokasi (radius)
            $jarak = $this->hitungJarak($request->latitude, $request->longitude, $lokasi->latitude, $lokasi->longitude);
            
            if ($jarak > $lokasi->radius) {
                return response()->json([
                    'success' => false, 
                    'message' => "Anda berada di luar radius presensi. Jarak: {$jarak}m (Max: {$lokasi->radius}m)"
                ]);
            }

            // Upload foto
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('presensi/masuk', 'public');
            }

            // Buat atau update presensi
            if (!$presensiHariIni) {
                $presensi = new Presensi();
                $presensi->peserta_id = $peserta->id;
                $presensi->tanggal = today();
                $presensi->jam_kerja_id = $jamKerja->id;
                $presensi->lokasi_id = $lokasi->id;
            } else {
                $presensi = $presensiHariIni;
            }

            $presensi->jam_masuk = now()->format('H:i:s');
            $presensi->latitude_masuk = $request->latitude;
            $presensi->longitude_masuk = $request->longitude;
            $presensi->foto_masuk = $fotoPath;
            $presensi->keterangan_masuk = $request->keterangan;

            // Hitung keterlambatan
            $presensi->keterlambatan = $this->hitungKeterlambatan($presensi->jam_masuk, $jamKerja->jam_masuk);
            
            // Tentukan status
            $presensi->status = $this->tentukanStatus($presensi->keterlambatan, $jamKerja->toleransi_keterlambatan);
            
            $presensi->save();

            return response()->json([
                'success' => true, 
                'message' => 'Presensi masuk berhasil dicatat.',
                'data' => [
                    'jam_masuk' => $presensi->jam_masuk,
                    'status' => $presensi->getStatusLabel(),
                    'keterlambatan' => $presensi->getKeterlambatanFormatted()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function absenKeluar(Request $request)
    {
        $user = Auth::user();
        $peserta = Peserta::where('user_id', $user->id)->first();
        
        if (!$peserta) {
            return response()->json(['success' => false, 'message' => 'Data peserta tidak ditemukan.']);
        }

        // Validasi sudah absen masuk
        $presensi = Presensi::where('peserta_id', $peserta->id)
            ->whereDate('tanggal', today())
            ->first();

        if (!$presensi || !$presensi->jam_masuk) {
            return response()->json(['success' => false, 'message' => 'Anda belum melakukan presensi masuk hari ini.']);
        }

        if ($presensi->jam_keluar) {
            return response()->json(['success' => false, 'message' => 'Anda sudah melakukan presensi keluar hari ini.']);
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'keterangan' => 'nullable|string|max:255'
        ]);

        try {
            $lokasi = $presensi->lokasi;

            // Validasi lokasi (radius)
            $jarak = $this->hitungJarak($request->latitude, $request->longitude, $lokasi->latitude, $lokasi->longitude);
            
            if ($jarak > $lokasi->radius) {
                return response()->json([
                    'success' => false, 
                    'message' => "Anda berada di luar radius presensi. Jarak: {$jarak}m (Max: {$lokasi->radius}m)"
                ]);
            }

            // Upload foto
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('presensi/keluar', 'public');
            }

            $presensi->jam_keluar = now()->format('H:i:s');
            $presensi->latitude_keluar = $request->latitude;
            $presensi->longitude_keluar = $request->longitude;
            $presensi->foto_keluar = $fotoPath;
            $presensi->keterangan_keluar = $request->keterangan;

            // Hitung durasi kerja
            $presensi->durasi_kerja = $this->hitungDurasiKerja($presensi->jam_masuk, $presensi->jam_keluar);
            
            $presensi->save();

            return response()->json([
                'success' => true, 
                'message' => 'Presensi keluar berhasil dicatat.',
                'data' => [
                    'jam_keluar' => $presensi->jam_keluar,
                    'durasi_kerja' => $presensi->getDurasiKerjaFormatted()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function riwayat()
    {
        $user = Auth::user();
        $peserta = Peserta::where('user_id', $user->id)->first();
        
        if (!$peserta) {
            return redirect()->route('dashboard')->with('error', 'Data peserta tidak ditemukan.');
        }

        $presensi = Presensi::where('peserta_id', $peserta->id)
            ->with(['jamKerja', 'lokasi'])
            ->orderBy('tanggal', 'desc')
            ->paginate(15);

        return view('peserta.presensi.riwayat', compact('presensi', 'peserta'));
    }

    public function detail($id)
    {
        $user = Auth::user();
        $peserta = Peserta::where('user_id', $user->id)->first();
        
        if (!$peserta) {
            return redirect()->route('dashboard')->with('error', 'Data peserta tidak ditemukan.');
        }

        $presensi = Presensi::where('id', $id)
            ->where('peserta_id', $peserta->id)
            ->with(['jamKerja', 'lokasi'])
            ->firstOrFail();

        return view('peserta.presensi.detail', compact('presensi', 'peserta'));
    }

    // Helper Methods
    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meter

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLatRad = deg2rad($lat2 - $lat1);
        $deltaLonRad = deg2rad($lon2 - $lon1);

        $a = sin($deltaLatRad / 2) * sin($deltaLatRad / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLonRad / 2) * sin($deltaLonRad / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return round($earthRadius * $c);
    }

    private function hitungKeterlambatan($jamMasukAktual, $jamMasukSeharusnya)
    {
        $masukAktual = Carbon::parse($jamMasukAktual);
        $masukSeharusnya = Carbon::parse($jamMasukSeharusnya);

        if ($masukAktual->gt($masukSeharusnya)) {
            return $masukSeharusnya->diffInMinutes($masukAktual);
        }

        return 0;
    }

    private function tentukanStatus($keterlambatan, $toleransi)
    {
        if ($keterlambatan > $toleransi) {
            return 'terlambat';
        }

        return 'hadir';
    }

    private function hitungDurasiKerja($jamMasuk, $jamKeluar)
    {
        $masuk = Carbon::parse($jamMasuk);
        $keluar = Carbon::parse($jamKeluar);

        if ($keluar->lt($masuk)) {
            $keluar->addDay();
        }

        return $masuk->diffInMinutes($keluar);
    }
}