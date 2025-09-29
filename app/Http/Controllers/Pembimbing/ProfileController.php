<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pembimbing;
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
        $pembimbing = $user->pembimbing;
        
        if (!$pembimbing) {
            return redirect()->route('login')->with('error', 'Data pembimbing tidak ditemukan');
        }
        
        return view('pembimbing.profile.index', compact('user', 'pembimbing'));
    }
    
    /**
     * Update profile information
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $pembimbing = $user->pembimbing;
        
        if (!$pembimbing) {
            return redirect()->route('login')->with('error', 'Data pembimbing tidak ditemukan');
        }
        
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'nip' => 'required|string|max:50|unique:pembimbing,nip,' . $pembimbing->id,
            'no_telepon' => 'required|string|max:15',
            'alamat' => 'nullable|string',
        ]);
        
        try {
            // Update user data using User model directly
            $userModel = User::find($user->id);
            $userModel->email = $request->email;
            $userModel->save();
            
            // Update pembimbing data
            $pembimbing->nama_lengkap = $request->nama_lengkap;
            $pembimbing->nip = $request->nip;
            $pembimbing->no_telepon = $request->no_telepon;
            $pembimbing->alamat = $request->alamat;
            $pembimbing->save();
            
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