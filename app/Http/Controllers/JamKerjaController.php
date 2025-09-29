<?php

namespace App\Http\Controllers;

use App\Models\JamKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JamKerjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jamKerja = JamKerja::orderBy('created_at', 'desc')->get();
        $user = Auth::user();
        
        return view('jam-kerja.index', compact('jamKerja', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        return view('jam-kerja.create', compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_shift' => 'required|string|max:100',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i|after:jam_masuk',
            'hari_kerja' => 'required|array|min:1',
            'hari_kerja.*' => 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'toleransi_keterlambatan' => 'required|integer|min:0|max:60',
            'keterangan' => 'nullable|string|max:255',
        ]);

        JamKerja::create([
            'nama_shift' => $request->nama_shift,
            'jam_masuk' => $request->jam_masuk,
            'jam_keluar' => $request->jam_keluar,
            'hari_kerja' => $request->hari_kerja,
            'toleransi_keterlambatan' => $request->toleransi_keterlambatan,
            'keterangan' => $request->keterangan,
            'is_active' => true,
        ]);

        return redirect()->route('admin.jam-kerja.index')->with('success', 'Jam kerja berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(JamKerja $jamKerja)
    {
        $user = Auth::user();
        return view('jam-kerja.show', compact('jamKerja', 'user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JamKerja $jamKerja)
    {
        $user = Auth::user();
        return view('jam-kerja.edit', compact('jamKerja', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JamKerja $jamKerja)
    {
        $request->validate([
            'nama_shift' => 'required|string|max:100',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i|after:jam_masuk',
            'hari_kerja' => 'required|array|min:1',
            'hari_kerja.*' => 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'toleransi_keterlambatan' => 'required|integer|min:0|max:60',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $jamKerja->update([
            'nama_shift' => $request->nama_shift,
            'jam_masuk' => $request->jam_masuk,
            'jam_keluar' => $request->jam_keluar,
            'hari_kerja' => $request->hari_kerja,
            'toleransi_keterlambatan' => $request->toleransi_keterlambatan,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('admin.jam-kerja.index')->with('success', 'Jam kerja berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JamKerja $jamKerja)
    {
        // Cek apakah jam kerja sedang digunakan oleh presensi
        // if ($jamKerja->presensi()->exists()) {
        //     return redirect()->route('admin.jam-kerja.index')->with('error', 'Jam kerja tidak dapat dihapus karena sedang digunakan');
        // }

        $jamKerja->delete();
        return redirect()->route('admin.jam-kerja.index')->with('success', 'Jam kerja berhasil dihapus');
    }

    /**
     * Toggle status jam kerja
     */
    public function toggleStatus(JamKerja $jamKerja)
    {
        $jamKerja->update([
            'is_active' => !$jamKerja->is_active
        ]);

        $status = $jamKerja->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('admin.jam-kerja.index')->with('success', "Jam kerja berhasil $status");
    }
}
