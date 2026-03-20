<?php

namespace App\Http\Controllers;

use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class HariLiburController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        return view('hari-libur.index', compact('user'));
    }

    /**
     * Get calendar events for FullCalendar
     */
    public function getEvents(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        
        $events = HariLibur::getForCalendar($start, $end);
        
        return response()->json($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Debug: log request data
            Log::info('Store request data:', $request->all());
            
            $validated = $request->validate([
                'nama_libur' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'deskripsi' => 'nullable|string',
                'jenis' => 'required|in:nasional,keagamaan,custom',
                'warna' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            ], [
                'nama_libur.required' => 'Nama hari libur wajib diisi',
                'tanggal.required' => 'Tanggal wajib diisi',
                'tanggal.date' => 'Format tanggal tidak valid',
                'jenis.required' => 'Jenis hari libur wajib dipilih',
                'jenis.in' => 'Jenis hari libur tidak valid',
                'warna.required' => 'Warna wajib dipilih',
                'warna.regex' => 'Format warna harus berupa hex color (#RRGGBB)',
            ]);

            // Check apakah tanggal sudah ada
            $existing = HariLibur::whereDate('tanggal', $validated['tanggal'])
                                 ->where('is_active', true)
                                 ->first();
            
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal ' . Carbon::parse($validated['tanggal'])->format('d/m/Y') . ' sudah ada hari libur: ' . $existing->nama_libur . '. Gunakan fungsi Edit untuk mengubah data.',
                    'existing_id' => $existing->id
                ], 422, ['Content-Type' => 'application/json']);
            }

            // Set default value for is_active if not provided
            $validated['is_active'] = $validated['is_active'] ?? true;

            $hariLibur = HariLibur::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Hari libur berhasil ditambahkan',
                'data' => [
                    'id' => $hariLibur->id,
                    'title' => $hariLibur->nama_libur,
                    'start' => Carbon::parse($hariLibur->tanggal)->format('Y-m-d'),
                    'color' => $hariLibur->warna,
                    'backgroundColor' => $hariLibur->warna,
                    'borderColor' => $hariLibur->warna,
                    'allDay' => true,
                    'extendedProps' => [
                        'description' => $hariLibur->deskripsi,
                        'jenis' => $hariLibur->jenis,
                        'warna' => $hariLibur->warna,
                    ]
                ]
            ], 200, ['Content-Type' => 'application/json']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->validator->errors()
            ], 422, ['Content-Type' => 'application/json']);
            
        } catch (\Exception $e) {
            Log::error('Store hari libur error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(HariLibur $hariLibur)
    {
        return response()->json([
            'success' => true,
            'data' => $hariLibur
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HariLibur $hariLibur)
    {
        try {
            // Debug: log request data
            Log::info('Update request data:', [
                'id' => $hariLibur->id,
                'request' => $request->all()
            ]);
            
            $validated = $request->validate([
                'nama_libur' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'deskripsi' => 'nullable|string',
                'jenis' => 'required|in:nasional,keagamaan,custom',
                'warna' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            ], [
                'nama_libur.required' => 'Nama hari libur wajib diisi',
                'tanggal.required' => 'Tanggal wajib diisi',
                'tanggal.date' => 'Format tanggal tidak valid',
                'jenis.required' => 'Jenis hari libur wajib dipilih',
                'jenis.in' => 'Jenis hari libur tidak valid',
                'warna.required' => 'Warna wajib dipilih',
                'warna.regex' => 'Format warna harus berupa hex color (#RRGGBB)',
            ]);

            // Check apakah tanggal sudah ada (kecuali record ini sendiri)
            $existing = HariLibur::whereDate('tanggal', $validated['tanggal'])
                                 ->where('is_active', true)
                                 ->where('id', '!=', $hariLibur->id)
                                 ->first();
            
            if ($existing) {
                Log::info('Update conflict found:', [
                    'editing_id' => $hariLibur->id,
                    'conflict_id' => $existing->id,
                    'tanggal' => $validated['tanggal']
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal ' . Carbon::parse($validated['tanggal'])->format('d/m/Y') . ' sudah ada hari libur: ' . $existing->nama_libur
                ], 422, ['Content-Type' => 'application/json']);
            }

            // Set default value for is_active if not provided
            $validated['is_active'] = $validated['is_active'] ?? true;

            $hariLibur->update($validated);
            $hariLibur->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Hari libur berhasil diupdate',
                'data' => [
                    'id' => $hariLibur->id,
                    'title' => $hariLibur->nama_libur,
                    'start' => Carbon::parse($hariLibur->tanggal)->format('Y-m-d'),
                    'color' => $hariLibur->warna,
                    'backgroundColor' => $hariLibur->warna,
                    'borderColor' => $hariLibur->warna,
                    'allDay' => true,
                    'extendedProps' => [
                        'description' => $hariLibur->deskripsi,
                        'jenis' => $hariLibur->jenis,
                        'warna' => $hariLibur->warna,
                    ]
                ]
            ], 200, ['Content-Type' => 'application/json']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->validator->errors()
            ], 422, ['Content-Type' => 'application/json']);
            
        } catch (\Exception $e) {
            Log::error('Update hari libur error: ' . $e->getMessage(), [
                'hari_libur_id' => $hariLibur->id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate data: ' . $e->getMessage()
            ], 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HariLibur $hariLibur)
    {
        try {
            $hariLibur->delete();

            return response()->json([
                'success' => true,
                'message' => 'Hari libur berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Delete hari libur error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle status aktif/non-aktif hari libur
     */
    public function toggleStatus(HariLibur $hariLibur)
    {
        try {
            $hariLibur->is_active = !$hariLibur->is_active;
            $hariLibur->save();

            return response()->json([
                'success' => true,
                'message' => 'Status hari libur berhasil diubah'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Toggle status hari libur error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import hari libur dari file JSON
     */
    public function importFromFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:json|max:2048',
                'replace_existing' => 'nullable|boolean'
            ]);

            $file = $request->file('file');
            $content = file_get_contents($file->getPathname());
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'File JSON tidak valid: ' . json_last_error_msg()
                ], 422);
            }

            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format file harus berupa array JSON'
                ], 422);
            }

            $imported = 0;
            $skipped = 0;
            $errors = [];
            $replaceExisting = $request->boolean('replace_existing', false);

            foreach ($data as $index => $item) {
                try {
                    if (!isset($item['nama_libur']) || !isset($item['tanggal'])) {
                        $errors[] = "Baris " . ($index + 1) . ": nama_libur dan tanggal wajib diisi";
                        continue;
                    }

                    $item['jenis'] = $item['jenis'] ?? 'nasional';
                    $item['warna'] = $item['warna'] ?? '#e74c3c';
                    $item['is_active'] = $item['is_active'] ?? true;

                    $existing = HariLibur::whereDate('tanggal', $item['tanggal'])->first();
                    
                    if ($existing) {
                        if ($replaceExisting) {
                            $existing->update($item);
                            $imported++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        HariLibur::create($item);
                        $imported++;
                    }

                } catch (\Exception $e) {
                    $errors[] = "Baris " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Import selesai. Berhasil: {$imported}, Dilewati: {$skipped}",
                'data' => [
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Import file error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export template untuk import
     */
    public function exportTemplate()
    {
        try {
            $template = [
                [
                    'nama_libur' => 'Tahun Baru',
                    'tanggal' => '2024-01-01',
                    'deskripsi' => 'Perayaan Tahun Baru Masehi',
                    'jenis' => 'nasional',
                    'warna' => '#e74c3c',
                    'is_active' => true
                ],
                [
                    'nama_libur' => 'Hari Kemerdekaan',
                    'tanggal' => '2024-08-17',
                    'deskripsi' => 'Hari Kemerdekaan Indonesia',
                    'jenis' => 'nasional',
                    'warna' => '#e74c3c',
                    'is_active' => true
                ]
            ];

            $filename = 'template_hari_libur_' . date('Y-m-d_H-i-s') . '.json';
            
            return response()->json($template, 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            Log::error('Export template error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sinkronisasi hari libur dari API eksternal
     * Endpoint: https://api-harilibur.vercel.app/api
     */
    public function syncFromAPI(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $month = $request->input('month'); // optional
            
            // Build API URL
            $apiUrl = 'https://api-harilibur.vercel.app/api';
            $params = [];
            
            if ($year) {
                $params['year'] = $year;
            }
            if ($month) {
                $params['month'] = $month;
            }
            
            // Fetch data dari API
            $response = Http::timeout(30)->get($apiUrl, $params);
            
            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data dari API. Status: ' . $response->status()
                ], 500);
            }
            
            $holidays = $response->json();
            
            if (!is_array($holidays)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format data API tidak valid'
                ], 500);
            }
            
            if (empty($holidays)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data hari libur untuk periode yang dipilih'
                ], 404);
            }
            
            $imported = 0;
            $updated = 0;
            $skipped = 0;
            $errors = [];
            
            foreach ($holidays as $holiday) {
                try {
                    // Validasi data dari API
                    if (!isset($holiday['holiday_date']) || !isset($holiday['holiday_name'])) {
                        $errors[] = "Data tidak lengkap: " . json_encode($holiday);
                        continue;
                    }
                    
                    // Parse tanggal
                    $tanggal = Carbon::parse($holiday['holiday_date']);
                    
                    // Tentukan jenis berdasarkan kategori dari API
                    $jenis = 'nasional'; // default
                    if (isset($holiday['is_national_holiday'])) {
                        $jenis = $holiday['is_national_holiday'] ? 'nasional' : 'keagamaan';
                    }
                    
                    // Map warna berdasarkan jenis
                    $warna = match($jenis) {
                        'nasional' => '#e74c3c',    // Merah untuk nasional
                        'keagamaan' => '#9b59b6',   // Ungu untuk keagamaan
                        default => '#3498db'         // Biru untuk lainnya
                    };
                    
                    $data = [
                        'nama_libur' => $holiday['holiday_name'],
                        'tanggal' => $tanggal->format('Y-m-d'),
                        'deskripsi' => 'Disinkronkan dari API Hari Libur Indonesia',
                        'jenis' => $jenis,
                        'warna' => $warna,
                        'is_active' => true
                    ];
                    
                    // Cek apakah sudah ada
                    $existing = HariLibur::whereDate('tanggal', $tanggal)->first();
                    
                    if ($existing) {
                        // Update jika dari API (bukan custom)
                        if ($existing->jenis !== 'custom') {
                            $existing->update($data);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        HariLibur::create($data);
                        $imported++;
                    }
                    
                } catch (\Exception $e) {
                    $errors[] = "Error processing: " . ($holiday['holiday_name'] ?? 'unknown') . " - " . $e->getMessage();
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Sinkronisasi selesai. Ditambahkan: {$imported}, Diupdate: {$updated}, Dilewati: {$skipped}",
                'data' => [
                    'imported' => $imported,
                    'updated' => $updated,
                    'skipped' => $skipped,
                    'errors' => $errors,
                    'total_holidays' => count($holidays)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Sync from API error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage()
            ], 500);
        }
    }
}
