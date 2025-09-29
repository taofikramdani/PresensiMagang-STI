<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use App\Models\User;
use App\Models\Pembimbing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class PesertaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Update status peserta yang sudah melewati periode magang
        Peserta::where('status', 'aktif')
            ->whereDate('tanggal_selesai', '<', now())
            ->update(['status' => 'non-aktif']);

        $query = Peserta::with(['user', 'pembimbing', 'pembimbingDetail', 'lokasi']);

        // Apply filters if any
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('jurusan', 'like', "%{$search}%")
                  ->orWhere('universitas', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('pembimbing_id')) {
            $query->whereHas('pembimbingDetail', function ($q) use ($request) {
                $q->where('user_id', $request->pembimbing_id);
            });
        }

        if ($request->filled('lokasi_id')) {
            $query->where('lokasi_id', $request->lokasi_id);
        }

        $peserta = $query->get();
        $user = Auth::user();
        
        // Data untuk filter dropdown
        $pembimbing = Pembimbing::with('user')->where('status', 'aktif')->get();
        $lokasi = \App\Models\Lokasi::where('is_active', true)->get();
        
        return view('peserta.index', compact('peserta', 'user', 'pembimbing', 'lokasi'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pembimbing = Pembimbing::with('user')->where('status', 'aktif')->get();
        $lokasi = \App\Models\Lokasi::where('is_active', true)->get();
        $user = Auth::user();
        
        return view('peserta.create', compact('pembimbing', 'lokasi', 'user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $request->validate([
        'nama_lengkap'   => 'required|string|max:255',
        'username'       => 'required|string|max:255|unique:users,name', 
        'email'          => 'required|string|email|max:255|unique:users',
        'password'       => [
            'required',
            'string',
            'min:8',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
        ],
        'nim'            => 'required|string|max:20|unique:peserta',
        'jurusan'        => 'required|string|max:100',
        'universitas'    => 'required|string|max:100',
        'no_hp'          => 'required|string|max:15',
        'alamat'         => 'required|string',
        'tanggal_mulai'  => 'required|date',
        'tanggal_selesai'=> 'required|date|after:tanggal_mulai',
        'pembimbing_id'  => 'required|exists:users,id',
        'lokasi_id'      => 'required|exists:lokasis,id'
    ], [
        'password.regex' => 'Password harus mengandung minimal 1 huruf kecil, 1 huruf besar, 1 angka, dan 1 simbol (@$!%*?&)'
    ]);

    // Buat user dulu
    $user = User::create([
        'name'     => $request->username, 
        'email'    => $request->email,
        'password' => Hash::make($request->password),
        'role'     => 'peserta'
    ]);

    // Buat peserta
    Peserta::create([
        'user_id'       => $user->id,
        'nama_lengkap'  => $request->nama_lengkap, 
        'nim'           => $request->nim,
        'jurusan'       => $request->jurusan,
        'universitas'   => $request->universitas,
        'no_telepon'    => $request->no_hp,
        'alamat'        => $request->alamat,
        'tanggal_mulai' => $request->tanggal_mulai,
        'tanggal_selesai' => $request->tanggal_selesai,
        'pembimbing_id' => $request->pembimbing_id,
        'lokasi_id'     => $request->lokasi_id,
        'status'        => 'aktif'
    ]);

    return redirect()->route('admin.peserta.index')
        ->with('success', 'Peserta berhasil ditambahkan!');
}


    /**
     * Display the specified resource.
     */
    public function show(Peserta $peserta)
    {
        $peserta->load(['user', 'pembimbingDetail']);
        $user = Auth::user();
        
        return view('peserta.show', compact('peserta', 'user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Peserta $peserta)
    {
        $peserta->load('user');
        $pembimbing = Pembimbing::with('user')->where('status', 'aktif')->get();
        $lokasi = \App\Models\Lokasi::where('is_active', true)->get();
        $user = Auth::user();
        
        return view('peserta.edit', compact('peserta', 'pembimbing', 'lokasi', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Peserta $peserta)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255|unique:peserta,nama_lengkap,' . $peserta->id,
            'username' => 'required|string|max:255|unique:users,name,' . $peserta->user_id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $peserta->user_id,
            'nim' => 'required|string|max:20|unique:peserta,nim,' . $peserta->id,
            'jurusan' => 'required|string|max:100',
            'universitas' => 'required|string|max:100',
            'no_hp' => 'required|string|max:15',
            'alamat' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'pembimbing_id' => 'required|exists:users,id',
            'lokasi_id' => 'required|exists:lokasis,id',
            'status' => 'required|in:aktif,non-aktif'
        ]);

        // Update user
        $peserta->user->update([
            'name' => $request->username,
            'email' => $request->email,
        ]);

        // Update password if provided
        if ($request->password) {
            $request->validate([
                'password' => [
                    'string',
                    'min:8',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
                ]
            ], [
                'password.regex' => 'Password harus mengandung minimal 1 huruf kecil, 1 huruf besar, 1 angka, dan 1 simbol (@$!%*?&)'
            ]);
            
            $peserta->user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        // Cek apakah tanggal selesai diperpanjang dari yang sebelumnya
        $tanggalSelesaiBaru = \Carbon\Carbon::parse($request->tanggal_selesai);
        $tanggalSelesaiLama = $peserta->tanggal_selesai ? \Carbon\Carbon::parse($peserta->tanggal_selesai) : null;
        $statusBaru = $request->status;
        
        // Jika tanggal selesai diperpanjang dan sekarang masih dalam periode aktif
        if ($tanggalSelesaiLama && $tanggalSelesaiBaru->isAfter($tanggalSelesaiLama)) {
            // Jika periode baru masih berlaku (tanggal selesai >= hari ini)
            if ($tanggalSelesaiBaru->isAfter(now()) || $tanggalSelesaiBaru->isToday()) {
                // Otomatis ubah status jadi aktif jika sebelumnya non-aktif karena habis masa
                if ($peserta->status === 'non-aktif') {
                    $statusBaru = 'aktif';
                }
            }
        }
        
        // Update peserta
        $peserta->update([
            'nama_lengkap' => $request->nama_lengkap, // Update nama lengkap juga
            'nim' => $request->nim,
            'jurusan' => $request->jurusan,
            'universitas' => $request->universitas,
            'no_telepon' => $request->no_hp, // Fix field mapping
            'alamat' => $request->alamat,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'pembimbing_id' => $request->pembimbing_id,
            'lokasi_id' => $request->lokasi_id,
            'status' => $statusBaru // Gunakan status yang sudah diperiksa
        ]);

        // Pesan success dengan informasi tambahan jika status berubah otomatis
        $successMessage = 'Data peserta berhasil diperbarui';
        
        if ($statusBaru === 'aktif' && $request->status === 'non-aktif' && $peserta->status === 'non-aktif') {
            $successMessage .= '. Status otomatis diubah menjadi AKTIF karena periode magang diperpanjang!';
        }
        
        return redirect()->route('admin.peserta.index')
                        ->with('success', $successMessage);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Peserta $peserta)
    {
        // Delete the associated user as well
        $peserta->user->delete();
        $peserta->delete();

        return redirect()->route('admin.peserta.index')
                        ->with('success', 'Data peserta berhasil dihapus');
    }

    public function exportExcel(Request $request)
    {
        Log::info('Export Excel Request:', $request->all());
        $query = Peserta::with(['user', 'pembimbing', 'pembimbingDetail']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('jurusan', 'like', "%{$search}%")
                  ->orWhere('universitas', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('pembimbing_id')) {
            // Filter berdasarkan pembimbing_id yang merujuk ke user_id pembimbing
            $query->whereHas('pembimbingDetail', function ($q) use ($request) {
                $q->where('user_id', $request->pembimbing_id);
            });
        }

        if ($request->filled('lokasi_id')) {
            $query->where('lokasi_id', $request->lokasi_id);
        }

        $peserta = $query->get();
        Log::info('Export Excel Results Count:', ['count' => $peserta->count()]);

        $response = response()->view('exports.peserta-excel', compact('peserta'));
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment; filename="data_peserta_' . date('Y-m-d_H-i-s') . '.xls"');
        
        return $response;
    }

    public function exportPdf(Request $request)
    {
        Log::info('Export PDF Request:', $request->all());
        $query = Peserta::with(['user', 'pembimbing', 'pembimbingDetail']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('jurusan', 'like', "%{$search}%")
                  ->orWhere('universitas', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('pembimbing_id')) {
            // Filter berdasarkan pembimbing_id yang merujuk ke user_id pembimbing
            $query->whereHas('pembimbingDetail', function ($q) use ($request) {
                $q->where('user_id', $request->pembimbing_id);
            });
        }

        if ($request->filled('lokasi_id')) {
            $query->where('lokasi_id', $request->lokasi_id);
        }

        $peserta = $query->get();
        Log::info('Export PDF Results Count:', ['count' => $peserta->count()]);

        $pdf = Pdf::loadView('exports.peserta-pdf', compact('peserta'));
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('Data_Peserta_Magang_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}
