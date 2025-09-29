<?php

namespace App\Http\Controllers;

use App\Models\Pembimbing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PembimbingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pembimbing = Pembimbing::with('user')->get();
        $user = Auth::user();
        
        return view('pembimbing.index', compact('pembimbing', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        return view('pembimbing.create', compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $request->validate([
        'nama_lengkap' => 'required|string|max:255',
        'username'     => 'required|string|max:255|unique:users,name', // cek ke kolom users.name
        'email'        => 'required|string|email|max:255|unique:users',
        'password'     => [
            'required',
            'string',
            'min:8',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
        ],
        'nip'          => 'required|string|max:20|unique:pembimbing',
        'jabatan'      => 'required|string|max:100',
        'unit_kerja'   => 'required|string|max:100',
        'no_hp'        => 'required|string|max:15',
        'alamat'       => 'required|string',
        'status'       => 'required|in:aktif,non_aktif'
    ], [
        'password.regex' => 'Password harus mengandung minimal 1 huruf kecil, 1 huruf besar, 1 angka, dan 1 simbol (@$!%*?&)'
    ]);

    // Buat akun user
    $user = User::create([
        'name'     => $request->username, // username
        'email'    => $request->email,
        'password' => Hash::make($request->password),
        'role'     => 'pembimbing',
    ]);

    // Buat profil pembimbing
    Pembimbing::create([
        'user_id'      => $user->id,
        'nip'          => $request->nip,
        'nama_lengkap' => $request->nama_lengkap, // nama asli pembimbing
        'jabatan'      => $request->jabatan,
        'departemen'   => $request->unit_kerja,
        'no_telepon'   => $request->no_hp,
        'alamat'       => $request->alamat,
        'status'       => $request->status
    ]);

    return redirect()->route('admin.pembimbing.index')
        ->with('success', 'Data pembimbing berhasil ditambahkan.');
}


    /**
     * Display the specified resource.
     */
    public function show(Pembimbing $pembimbing)
    {
        $pembimbing->load(['user', 'peserta']);
        $user = Auth::user();
        
        return view('pembimbing.show', compact('pembimbing', 'user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pembimbing $pembimbing)
    {
        $pembimbing->load('user');
        $user = Auth::user();
        
        return view('pembimbing.edit', compact('pembimbing', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pembimbing $pembimbing)
{
    $request->validate([
        'nama_lengkap' => 'required|string|max:255',
        'username'     => 'required|string|max:255|unique:users,name,' . $pembimbing->user_id, // cek ke users.name
        'email'        => 'required|string|email|max:255|unique:users,email,' . $pembimbing->user_id,
        'password'     => [
            'nullable',
            'string',
            'min:8',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
        ],
        'nip'          => 'required|string|max:20|unique:pembimbing,nip,' . $pembimbing->id,
        'jabatan'      => 'required|string|max:100',
        'unit_kerja'   => 'required|string|max:100',
        'no_hp'        => 'required|string|max:15',
        'alamat'       => 'required|string',
        'status'       => 'required|in:aktif,non_aktif'
    ], [
        'password.regex' => 'Password harus mengandung minimal 1 huruf kecil, 1 huruf besar, 1 angka, dan 1 simbol (@$!%*?&)'
    ]);

    // Update user account
    $userData = [
        'name'  => $request->username, // username masuk ke users.name
        'email' => $request->email,
    ];

    if ($request->filled('password')) {
        $userData['password'] = Hash::make($request->password);
    }

    $pembimbing->user->update($userData);

    // Update pembimbing profile
    $pembimbing->update([
        'nip'          => $request->nip,
        'nama_lengkap' => $request->nama_lengkap,
        'jabatan'      => $request->jabatan,
        'departemen'   => $request->unit_kerja,
        'no_telepon'   => $request->no_hp,
        'alamat'       => $request->alamat,
        'status'       => $request->status
    ]);

    return redirect()->route('admin.pembimbing.index')
        ->with('success', 'Data pembimbing berhasil diperbarui.');
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pembimbing $pembimbing)
    {
        // Check if pembimbing has peserta
        if ($pembimbing->peserta()->count() > 0) {
            return redirect()->route('admin.pembimbing.index')
                ->with('error', 'Tidak dapat menghapus pembimbing yang masih memiliki peserta.');
        }

        // Delete user account and pembimbing profile
        $pembimbing->user->delete();
        $pembimbing->delete();

        return redirect()->route('admin.pembimbing.index')
            ->with('success', 'Data pembimbing berhasil dihapus.');
    }

    /**
     * Toggle pembimbing status
     */
    public function toggleStatus(Pembimbing $pembimbing)
    {
        $newStatus = $pembimbing->status === 'aktif' ? 'non_aktif' : 'aktif';
        $pembimbing->update(['status' => $newStatus]);

        return redirect()->route('admin.pembimbing.index')
            ->with('success', "Status pembimbing berhasil diubah menjadi {$newStatus}.");
    }
}
