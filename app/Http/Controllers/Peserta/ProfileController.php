<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the profile page
     */
    public function index()
    {
        $user = Auth::user();
        $peserta = $user->peserta;
        
        if (!$peserta) {
            return redirect()->route('login')->with('error', 'Data peserta tidak ditemukan');
        }
        
        // Load pembimbing relationship
        $peserta->load(['pembimbing', 'pembimbingDetail']);
        
        return view('peserta.profile.index', compact('user', 'peserta'));
    }
    
    /**
     * Update profile information
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $peserta = $user->peserta;
        
        if (!$peserta) {
            return redirect()->route('login')->with('error', 'Data peserta tidak ditemukan');
        }
        
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'universitas' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'nim' => 'required|string|max:20|unique:peserta,nim,' . $peserta->id,
            'alamat' => 'required|string',
            'no_telepon' => 'required|string|max:15',
        ]);
        
        try {
            // Update user data using User model directly
            $userModel = User::find($user->id);
            $userModel->email = $request->email;
            $userModel->save();
            
            // Update peserta data
            $peserta->nama_lengkap = $request->nama_lengkap;
            $peserta->universitas = $request->universitas;
            $peserta->jurusan = $request->jurusan;
            $peserta->nim = $request->nim;
            $peserta->alamat = $request->alamat;
            $peserta->no_telepon = $request->no_telepon;
            $peserta->save();
            
            return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui profil.');
        }
    }
    
    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);
        
        /** @var User $user */
        $user = Auth::user();
        
        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Password saat ini tidak benar.']);
        }
        
        try {
            // Update password using User model directly
            $userModel = User::find($user->id);
            $userModel->password = Hash::make($request->new_password);
            $userModel->save();
            
            return redirect()->back()->with('success', 'Password berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui password.');
        }
    }
}