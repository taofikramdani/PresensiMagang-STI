<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Lokasi;
use App\Models\Presensi;
use App\Models\Peserta;
use App\Models\JamKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PresensiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $peserta = Peserta::with('lokasi')->where('user_id', $user->id)->first();
        
        if (!$peserta) {
            return redirect()->route('peserta.dashboard')->with('error', 'Data peserta tidak ditemukan.');
        }

        // Get presensi hari ini (menggunakan timezone Jakarta)
        $hariIniJakarta = Carbon::now('Asia/Jakarta')->toDateString();
        $presensiHariIni = Presensi::where('peserta_id', $peserta->id)
            ->whereDate('tanggal', $hariIniJakarta)
            ->first();

        // Cek apakah hari ini sudah ada status izin/sakit dari perizinan
        $sudahIzinHariIni = $presensiHariIni && in_array($presensiHariIni->status, ['izin', 'sakit']);

        // Get jam kerja aktif berdasarkan hari ini
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
        
        $jamKerja = JamKerja::where('is_active', true)
            ->get()
            ->first(function ($jk) use ($hariIni) {
                $hariKerja = $jk->hari_kerja ?? [];
                return in_array($hariIni, $hariKerja);
            });
        
        // Fallback ke jam kerja aktif pertama jika tidak ada yang cocok
        if (!$jamKerja) {
            $jamKerja = JamKerja::aktif()->first();
        }
        
        // Get lokasi peserta
        $lokasi = $peserta->lokasi;
        
        // Default location if peserta doesn't have assigned location
        if (!$lokasi) {
            $lokasi = (object) [
                'nama_lokasi' => 'Belum Ada Lokasi',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'radius' => 100,
                'alamat' => 'Silakan hubungi admin untuk pengaturan lokasi'
            ];
        }
        
        return view('peserta.presensi.index', compact('presensiHariIni', 'lokasi', 'jamKerja', 'peserta', 'sudahIzinHariIni'));
    } 
    
    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'action' => 'required|in:checkin,checkout',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'use_default_location' => 'nullable|boolean'
        ]);
        
        try {
            $user = Auth::user();
            $peserta = Peserta::where('user_id', $user->id)->first();
            
            if (!$peserta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peserta tidak ditemukan'
                ], 400);
            }
            
            // Get peserta's assigned location
            $lokasi = $peserta->lokasi;
            if (!$lokasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum memiliki lokasi magang yang ditentukan. Silakan hubungi admin.'
                ], 400);
            }
            
            if (!$lokasi->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi magang Anda sedang tidak aktif. Silakan hubungi admin.'
                ], 400);
            }
            
            // Check if within radius (skip validation if using default location)
            $useDefaultLocation = $request->boolean('use_default_location', false);
            
            if (!$useDefaultLocation) {
                // Calculate distance only if not using default location
                $distance = $this->calculateDistance(
                    $request->latitude,
                    $request->longitude,
                    $lokasi->latitude,
                    $lokasi->longitude
                );
                
                // Check if within radius
                if ($distance > $lokasi->radius) {
                    return response()->json([
                        'success' => false,
                        'message' => "Anda terlalu jauh dari lokasi presensi (jarak: " . round($distance) . "m, maksimal: {$lokasi->radius}m)"
                    ], 400);
                }
            } else {
                // When using default location, set distance to 0 for acceptance
                $distance = 0;
                // Use peserta's assigned location coordinates
                $request->merge([
                    'latitude' => $lokasi->latitude,
                    'longitude' => $lokasi->longitude
                ]);
            }
            
            // Handle photo upload (now optional)
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $filename = time() . '_' . $user->id . '_' . $request->action . '.jpg';
                $photoPath = $photo->storeAs('presensi', $filename, 'public');
            }
            
            $today = Carbon::today();
            $now = Carbon::now();
            
            if ($request->action === 'checkin') {
                return $this->processCheckIn($peserta, $lokasi, $request, $photoPath, $distance);
            } else {
                return $this->processCheckOut($peserta, $request, $photoPath, $distance);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function processCheckIn($peserta, $lokasi, $request, $photoPath, $distance)
    {
        // Validasi sudah absen hari ini (timezone Jakarta)
        $hariIniJakarta = Carbon::now('Asia/Jakarta')->toDateString();
        $presensiHariIni = Presensi::where('peserta_id', $peserta->id)
            ->whereDate('tanggal', $hariIniJakarta)
            ->first();

        if ($presensiHariIni && $presensiHariIni->jam_masuk) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan presensi masuk hari ini.'
            ]);
        }

        // Cek apakah hari ini sudah ada status izin/sakit
        if ($presensiHariIni && in_array($presensiHariIni->status, ['izin', 'sakit'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sedang dalam status ' . $presensiHariIni->status . ' hari ini.'
            ]);
        }

        // Get jam kerja aktif berdasarkan hari ini
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
        
        $jamKerja = JamKerja::where('is_active', true)
            ->get()
            ->first(function ($jk) use ($hariIni) {
                $hariKerja = $jk->hari_kerja ?? [];
                return in_array($hariIni, $hariKerja);
            });
            
        if (!$jamKerja) {
            return response()->json([
                'success' => false,
                'message' => "Hari ini bukan hari kerja."
            ]);
        }

        // Validasi hari kerja (sudah dipastikan di atas)
        $hariKerja = $jamKerja->hari_kerja ?? [];
        
        // Debug log (bisa dihapus nanti)
        Log::info('Debug hari kerja', [
            'hari_ini_indonesian' => $hariIni,
            'hari_kerja_config' => $hariKerja,
            'jam_kerja_selected' => $jamKerja->nama_shift
        ]);

        // Buat atau update presensi
        if (!$presensiHariIni) {
            $presensi = Presensi::create([
                'peserta_id' => $peserta->id,
                'tanggal' => $hariIniJakarta,
                'jam_kerja_id' => $jamKerja->id,
                'lokasi_id' => $lokasi->id,
                'jam_masuk' => Carbon::now('Asia/Jakarta')->format('H:i:s'),
                'latitude_masuk' => $request->latitude,
                'longitude_masuk' => $request->longitude,
                'foto_masuk' => $photoPath,
                'keterlambatan' => $this->hitungKeterlambatan(Carbon::now('Asia/Jakarta')->format('H:i:s'), $jamKerja->jam_masuk),
                'status' => $this->tentukanStatus($this->hitungKeterlambatan(Carbon::now('Asia/Jakarta')->format('H:i:s'), $jamKerja->jam_masuk), $jamKerja->toleransi_keterlambatan),
            ]);
        } else {
            $presensi = $presensiHariIni;
            $presensi->jam_masuk = Carbon::now('Asia/Jakarta')->format('H:i:s');
            $presensi->latitude_masuk = $request->latitude;
            $presensi->longitude_masuk = $request->longitude;
            $presensi->foto_masuk = $photoPath;
            $presensi->keterlambatan = $this->hitungKeterlambatan($presensi->jam_masuk, $jamKerja->jam_masuk);
            $presensi->status = $this->tentukanStatus($presensi->keterlambatan, $jamKerja->toleransi_keterlambatan);
            $presensi->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil dicatat pada ' . $presensi->jam_masuk,
            'data' => [
                'action' => 'checkin',
                'jam_masuk' => $presensi->jam_masuk,
                'status' => $presensi->getStatusLabel(),
                'keterlambatan' => $presensi->getKeterlambatanFormatted(),
                'distance' => round($distance)
            ]
        ]);
    }
    
    private function processCheckOut($peserta, $request, $photoPath, $distance)
    {
        // Validasi sudah absen masuk (timezone Jakarta)
        $hariIniJakarta = Carbon::now('Asia/Jakarta')->toDateString();
        $presensi = Presensi::where('peserta_id', $peserta->id)
            ->whereDate('tanggal', $hariIniJakarta)
            ->first();

        if (!$presensi || !$presensi->jam_masuk) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan presensi masuk hari ini.'
            ]);
        }

        // Cek apakah dalam status izin/sakit
        if (in_array($presensi->status, ['izin', 'sakit'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sedang dalam status ' . $presensi->status . ' hari ini.'
            ]);
        }

        // Cek apakah ini checkout pertama atau checkout ulang
        $isCheckoutUlang = (bool) $presensi->jam_keluar;
        
        // Update jam keluar dengan waktu terbaru (checkout terakhir)
        $presensi->jam_keluar = Carbon::now('Asia/Jakarta')->format('H:i:s');
        $presensi->latitude_keluar = $request->latitude;
        $presensi->longitude_keluar = $request->longitude;
        
        // Update foto keluar (foto terakhir yang akan disimpan)
        if ($photoPath) {
            // Hapus foto lama jika ada dan bukan checkout pertama
            if ($isCheckoutUlang && $presensi->foto_keluar && Storage::disk('public')->exists($presensi->foto_keluar)) {
                Storage::disk('public')->delete($presensi->foto_keluar);
            }
            $presensi->foto_keluar = $photoPath;
        }

        // Hitung ulang durasi kerja berdasarkan jam keluar terbaru
        $presensi->durasi_kerja = $this->hitungDurasiKerja($presensi->jam_masuk, $presensi->jam_keluar);
        
        $presensi->save();

        $message = $isCheckoutUlang 
            ? 'Check-out berhasil diperbarui pada ' . $presensi->jam_keluar 
            : 'Check-out berhasil dicatat pada ' . $presensi->jam_keluar;

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'action' => 'checkout',
                'jam_keluar' => $presensi->jam_keluar,
                'durasi_kerja' => $presensi->getDurasiKerjaFormatted(),
                'distance' => round($distance)
            ]
        ]);
    }
    
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
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

    public function checkin(Request $request)
    {
        $request->merge(['action' => 'checkin']);
        return $this->store($request);
    }
    
    public function checkout(Request $request)
    {
        $request->merge(['action' => 'checkout']);
        return $this->store($request);
    }

    /**
     * Check if peserta has filled kegiatan for today
     */
    public function checkKegiatanHarian()
    {
        try {
            $user = Auth::user();
            $peserta = Peserta::where('user_id', $user->id)->first();
            
            if (!$peserta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peserta tidak ditemukan'
                ]);
            }

            // Get today's date in Jakarta timezone
            $hariIniJakarta = Carbon::now('Asia/Jakarta')->toDateString();
            
            // Check if there's any kegiatan for today
            $kegiatanHariIni = \App\Models\Kegiatan::where('peserta_id', $peserta->id)
                ->whereDate('tanggal', $hariIniJakarta)
                ->count();

            return response()->json([
                'success' => true,
                'hasKegiatan' => $kegiatanHariIni > 0,
                'jumlahKegiatan' => $kegiatanHariIni,
                'tanggal' => $hariIniJakarta
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking kegiatan harian: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengecek kegiatan'
            ]);
        }
    }
    
    /**
     * Get peserta's default location coordinates
     */
    public function getDefaultLocation()
    {
        try {
            $user = Auth::user();
            $peserta = Peserta::with('lokasi')->where('user_id', $user->id)->first();
            
            if (!$peserta || !$peserta->lokasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi magang tidak ditemukan'
                ], 400);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'latitude' => $peserta->lokasi->latitude,
                    'longitude' => $peserta->lokasi->longitude,
                    'radius' => $peserta->lokasi->radius,
                    'nama_lokasi' => $peserta->lokasi->nama_lokasi,
                    'alamat' => $peserta->lokasi->alamat
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if current connection is using HTTPS
     */
    public function checkSSLStatus()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'is_https' => request()->secure() || request()->header('x-forwarded-proto') === 'https',
                'protocol' => request()->secure() || request()->header('x-forwarded-proto') === 'https' ? 'https' : 'http'
            ]
        ]);
    }
}
