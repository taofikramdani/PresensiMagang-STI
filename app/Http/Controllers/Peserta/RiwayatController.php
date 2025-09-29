<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Peserta;
use App\Models\Presensi;
use App\Models\JamKerja;
use App\Models\Perizinan;
use Carbon\Carbon;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $peserta = Peserta::where('user_id', $user->id)->first();
        
        if (!$peserta) {
            return redirect()->route('peserta.dashboard')->with('error', 'Data peserta tidak ditemukan.');
        }

        // Gunakan periode magang peserta
        $startDate = Carbon::parse($peserta->tanggal_mulai);
        $endDate = Carbon::parse($peserta->tanggal_selesai);
        $today = Carbon::now('Asia/Jakarta');
        
        // Jangan tampilkan data melewati hari ini atau tanggal selesai
        $actualEndDate = $endDate->lt($today) ? $endDate : $today->copy()->startOfDay();

        // Get filter bulan dari request atau default bulan ini untuk tampilan
        $bulan = $request->get('bulan', Carbon::now('Asia/Jakarta')->format('Y-m'));
        $filterStartDate = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $filterEndDate = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth();
        
        // Batasi filter berdasarkan periode magang
        $filterStartDate = $filterStartDate->lt($startDate) ? $startDate : $filterStartDate;
        $filterEndDate = $filterEndDate->gt($actualEndDate) ? $actualEndDate : $filterEndDate;

        // Get riwayat presensi dalam bulan yang dipilih (dalam periode magang)
        $riwayatPresensi = Presensi::where('peserta_id', $peserta->id)
            ->whereBetween('tanggal', [$filterStartDate->toDateString(), $filterEndDate->toDateString()])
            ->orderBy('tanggal', 'desc')
            ->with(['jamKerja', 'lokasi'])
            ->paginate(20);

        // Hitung statistik berdasarkan periode magang lengkap
        $statistik = $this->hitungStatistik($peserta->id, $startDate, $actualEndDate);

        // Generate list bulan untuk dropdown filter berdasarkan periode magang
        $daftarBulan = $this->generateDaftarBulan($peserta);

        return view('peserta.riwayat.index', compact(
            'riwayatPresensi',
            'statistik', 
            'bulan',
            'daftarBulan',
            'peserta',
            'startDate',
            'actualEndDate'
        ));
    }

    private function hitungStatistik($pesertaId, $startDate, $endDate)
    {
        $presensi = Presensi::where('peserta_id', $pesertaId)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $totalPresensi = $presensi->count();
        $hadir = $presensi->where('status', 'hadir')->count();
        $terlambat = $presensi->where('status', 'terlambat')->count();
        $izin = $presensi->where('status', 'izin')->count();
        $sakit = $presensi->where('status', 'sakit')->count();
        $alpaFromDb = $presensi->where('status', 'alpa')->count();

        // Hitung total hari kerja dalam bulan (exclude weekend jika ada)
        $totalHariKerja = $this->hitungHariKerja($startDate, $endDate);
        
        // Hitung alpa: hari kerja yang tidak ada data presensi sama sekali
        // Hari dengan izin/sakit tidak dihitung sebagai alpa
        $hariDenganPresensi = $hadir + $terlambat + $izin + $sakit + $alpaFromDb;
        $hariTanpaPresensi = max(0, $totalHariKerja - $hariDenganPresensi);
        $totalAlpa = $alpaFromDb + $hariTanpaPresensi;

        return [
            'total_presensi' => $totalPresensi,
            'total_hari' => $totalPresensi,
            'hadir' => $hadir,
            'tepat_waktu' => $hadir, // hadir = tepat waktu
            'terlambat' => $terlambat,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpha' => $totalAlpa, // Total alpa termasuk hari tanpa presensi
            'total_hari_kerja' => $totalHariKerja,
            'persentase_kehadiran' => $totalHariKerja > 0 ? round((($hadir + $terlambat) / $totalHariKerja) * 100, 1) : 0
        ];
    }

    private function hitungHariKerja($startDate, $endDate)
    {
        // Hitung hari kerja berdasarkan periode magang
        $hariKerja = 0;
        $current = $startDate->copy();
        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        
        // Jangan hitung hari kerja di masa depan
        $actualEndDate = $endDate->gt($today) ? $today : $endDate;
        
        while ($current <= $actualEndDate) {
            // Hitung hari Senin-Jumat sebagai hari kerja (sesuai jam kerja aktif)
            if ($current->isWeekday()) {
                // Bisa ditambahkan pengecekan jadwal kerja aktif jika ada
                $hariKerja++;
            }
            $current->addDay();
        }
        
        return $hariKerja;
    }

    private function generateDaftarBulan($peserta)
    {
        $daftarBulan = [];
        $magangStart = Carbon::parse($peserta->tanggal_mulai);
        $magangEnd = Carbon::parse($peserta->tanggal_selesai);
        $now = Carbon::now('Asia/Jakarta');
        
        // Batasi sampai hari ini jika belum selesai magang
        $actualEnd = $magangEnd->gt($now) ? $now : $magangEnd;
        
        $current = $magangStart->copy()->startOfMonth();
        
        // Generate bulan-bulan dalam periode magang
        while ($current <= $actualEnd) {
            $daftarBulan[] = [
                'value' => $current->format('Y-m'),
                'label' => $current->locale('id')->isoFormat('MMMM Y')
            ];
            $current->addMonth();
        }
        
        // Reverse agar bulan terbaru di atas
        return array_reverse($daftarBulan);
    }

    public function detail($id)
    {
        $user = Auth::user();
        $peserta = Peserta::where('user_id', $user->id)->first();
        
        if (!$peserta) {
            return redirect()->route('peserta.dashboard')->with('error', 'Data peserta tidak ditemukan.');
        }

        $presensi = Presensi::where('peserta_id', $peserta->id)
            ->where('id', $id)
            ->with(['jamKerja', 'lokasi'])
            ->first();

        if (!$presensi) {
            return redirect()->route('peserta.riwayat.index')->with('error', 'Data presensi tidak ditemukan.');
        }

        return view('peserta.riwayat.detail', compact('presensi', 'peserta'));
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $peserta = Peserta::where('user_id', $user->id)->first();
        
        if (!$peserta) {
            return redirect()->route('peserta.dashboard')->with('error', 'Data peserta tidak ditemukan.');
        }

        // Get filter bulan dari request atau default bulan ini
        $bulan = $request->get('bulan', Carbon::now('Asia/Jakarta')->format('Y-m'));
        $startDate = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth();

        // Batasi berdasarkan periode magang peserta
        $tanggalMulaiMagang = $peserta->tanggal_mulai ? Carbon::parse($peserta->tanggal_mulai) : null;
        $tanggalSelesaiMagang = $peserta->tanggal_selesai ? Carbon::parse($peserta->tanggal_selesai) : null;

        // Adjust start and end date berdasarkan periode magang
        if ($tanggalMulaiMagang && $startDate->lt($tanggalMulaiMagang)) {
            $startDate = $tanggalMulaiMagang->copy();
        }
        if ($tanggalSelesaiMagang && $endDate->gt($tanggalSelesaiMagang)) {
            $endDate = $tanggalSelesaiMagang->copy();
        }

        // Get riwayat presensi dalam periode yang sudah disesuaikan
        $presensiData = Presensi::where('peserta_id', $peserta->id)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->with(['jamKerja', 'lokasi'])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->tanggal)->toDateString();
            }); // Key by tanggal dalam format Y-m-d

        // Generate complete data dengan alpha untuk hari kerja yang tidak ada presensi
        $riwayatPresensi = collect();
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $tanggalString = $currentDate->toDateString();
            
            // Skip weekend (Sabtu=6, Minggu=0)
            if (!in_array($currentDate->dayOfWeek, [0, 6])) {
                if ($presensiData->has($tanggalString)) {
                    // Ada data presensi
                    $riwayatPresensi->push($presensiData[$tanggalString]);
                } else {
                    // Tidak ada data presensi
                    if ($tanggalString <= $today) {
                        // Tanggal sudah lewat atau hari ini, tandai sebagai alpha
                        $alphaEntry = [
                            'tanggal' => $tanggalString,
                            'jam_masuk' => null,
                            'jam_keluar' => null,
                            'status' => 'alpha',
                            'keterangan' => 'Tidak ada data presensi',
                            'lokasi' => $peserta->lokasi, // Default lokasi peserta
                            'is_alpha_entry' => true // Flag untuk identifikasi
                        ];
                        
                        $riwayatPresensi->push((object) $alphaEntry); // Convert to object
                    } else {
                        // Tanggal belum datang, buat entry kosong
                        $emptyEntry = [
                            'tanggal' => $tanggalString,
                            'jam_masuk' => null,
                            'jam_keluar' => null,
                            'status' => null,
                            'keterangan' => null,
                            'lokasi' => $peserta->lokasi, // Default lokasi peserta
                            'is_future_entry' => true // Flag untuk identifikasi
                        ];
                        
                        $riwayatPresensi->push((object) $emptyEntry); // Convert to object
                    }
                }
            }
            
            $currentDate->addDay();
        }

        // Hitung statistik bulan ini
        $statistik = $this->hitungStatistik($peserta->id, $startDate, $endDate);

        // Format periode untuk display
        $periode = Carbon::createFromFormat('Y-m', $bulan)->locale('id')->isoFormat('MMMM Y');
        
        // Format periode magang
        $periodeMagang = null;
        if ($peserta->tanggal_mulai && $peserta->tanggal_selesai) {
            $tanggalMulai = Carbon::parse($peserta->tanggal_mulai);
            $tanggalSelesai = Carbon::parse($peserta->tanggal_selesai);
            $periodeMagang = $tanggalMulai->format('d/m/Y') . ' - ' . $tanggalSelesai->format('d/m/Y');
        }

        // Load peserta with lokasi relationship
        $peserta->load('lokasi');
        
        // Load PDF view
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('peserta.riwayat.pdf', compact(
            'riwayatPresensi',
            'statistik',
            'peserta', 
            'periode',
            'startDate',
            'endDate',
            'periodeMagang'
        ));

        // Set paper size dan orientation
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'Rekap_Presensi_' . $peserta->nama_lengkap . '_' . $bulan . '.pdf';
        
        return $pdf->download($filename);
    }
}
