<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdministratorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'admin');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $administrators = $query->latest()->paginate(10);
        $user = Auth::user();

        return view('admin.administrator.index', compact('administrators', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        return view('admin.administrator.create', compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
            ],
            'is_active' => 'boolean'
        ], [
            'password.regex' => 'Password harus mengandung minimal 1 huruf kecil, 1 huruf besar, 1 angka, dan 1 simbol (@$!%*?&)'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.administrator.index')
            ->with('success', 'Administrator berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $administrator)
    {
        // Ensure the user is an admin
        if ($administrator->role !== 'admin') {
            abort(404);
        }

        $user = Auth::user();
        return view('admin.administrator.show', compact('administrator', 'user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $administrator)
    {
        // Ensure the user is an admin
        if ($administrator->role !== 'admin') {
            abort(404);
        }

        $user = Auth::user();
        return view('admin.administrator.edit', compact('administrator', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $administrator)
    {
        // Ensure the user is an admin
        if ($administrator->role !== 'admin') {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($administrator->id),
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
            ],
            'is_active' => 'boolean'
        ], [
            'password.regex' => 'Password harus mengandung minimal 1 huruf kecil, 1 huruf besar, 1 angka, dan 1 simbol (@$!%*?&)'
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'is_active' => $request->has('is_active') ? true : false,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $administrator->update($updateData);

        return redirect()->route('admin.administrator.index')
            ->with('success', 'Data administrator berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $administrator)
    {
        // Ensure the user is an admin
        if ($administrator->role !== 'admin') {
            abort(404);
        }

        // Prevent deleting current user
        if ($administrator->id === Auth::id()) {
            return redirect()->route('admin.administrator.index')
                ->with('error', 'Tidak dapat menghapus akun administrator yang sedang digunakan.');
        }

        $administrator->delete();

        return redirect()->route('admin.administrator.index')
            ->with('success', 'Administrator berhasil dihapus.');
    }

    /**
     * Toggle administrator status
     */
    public function toggleStatus(User $administrator)
    {
        // Ensure the user is an admin
        if ($administrator->role !== 'admin') {
            abort(404);
        }

        // Prevent deactivating current user
        if ($administrator->id === Auth::id()) {
            return redirect()->route('admin.administrator.index')
                ->with('error', 'Tidak dapat menonaktifkan akun administrator yang sedang digunakan.');
        }

        $administrator->update([
            'is_active' => !$administrator->is_active
        ]);

        $status = $administrator->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.administrator.index')
            ->with('success', "Administrator berhasil {$status}.");
    }
}