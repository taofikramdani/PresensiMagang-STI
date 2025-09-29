@extends('layouts.main')

@section('title', 'Detail Pembimbing - Presensi Magang')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Pembimbing</h1>
                <p class="text-gray-600 mt-1">Informasi lengkap pembimbing: {{ $pembimbing->user->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.pembimbing.edit', $pembimbing) }}" 
                   class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-md transition-colors duration-200">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
                <a href="{{ route('admin.pembimbing.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-8 text-center">
                    <div class="h-24 w-24 bg-white rounded-full text-blue-600 flex items-center justify-center font-bold text-3xl mx-auto mb-4">
                        {{ strtoupper(substr($pembimbing->user->name, 0, 2)) }}
                    </div>
                    <h3 class="text-xl font-semibold text-white">{{ $pembimbing->user->name }}</h3>
                    <p class="text-blue-100 mt-1">{{ $pembimbing->jabatan }}</p>
                    <div class="mt-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                            {{ $pembimbing->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <i class="fas {{ $pembimbing->status === 'aktif' ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                            {{ ucfirst($pembimbing->status) }}
                        </span>
                    </div>
                </div>
                
                <div class="px-6 py-4">
                    <div class="space-y-3">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-id-badge w-5 mr-3 text-gray-400"></i>
                            <span class="text-sm">NIP: {{ $pembimbing->nip }}</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-building w-5 mr-3 text-gray-400"></i>
                            <span class="text-sm">{{ $pembimbing->departemen }}</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-envelope w-5 mr-3 text-gray-400"></i>
                            <span class="text-sm">{{ $pembimbing->email_kantor }}</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-phone w-5 mr-3 text-gray-400"></i>
                            <span class="text-sm">{{ $pembimbing->no_telepon }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Aksi Cepat</h3>
                <div class="space-y-3">
                    <form action="{{ route('admin.pembimbing.toggle-status', $pembimbing) }}" method="POST" class="w-full">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200
                                {{ $pembimbing->status === 'aktif' ? 'bg-orange-100 text-orange-800 hover:bg-orange-200' : 'bg-green-100 text-green-800 hover:bg-green-200' }}"
                                onclick="return confirm('Yakin ingin mengubah status pembimbing ini?')">
                            <i class="fas {{ $pembimbing->status === 'aktif' ? 'fa-pause' : 'fa-play' }} mr-2"></i>
                            {{ $pembimbing->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>

                    <form action="{{ route('admin.pembimbing.destroy', $pembimbing) }}" method="POST" class="w-full">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full flex items-center justify-center px-4 py-2 bg-red-100 text-red-800 hover:bg-red-200 text-sm font-medium rounded-md transition-colors duration-200"
                                onclick="return confirm('Yakin ingin menghapus data pembimbing ini? Tindakan ini tidak dapat dibatalkan.')">
                            <i class="fas fa-trash mr-2"></i>
                            Hapus Data
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Detail Information -->
        <div class="lg:col-span-2">
            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-user mr-2 text-blue-600"></i>
                        Informasi Pribadi
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nama Lengkap</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pembimbing->nama_lengkap }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">NIP</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pembimbing->nip }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pembimbing->email_kantor }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Username</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pembimbing->user->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">No. HP/Telepon</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pembimbing->no_telepon }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $pembimbing->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($pembimbing->status) }}
                                </span>
                            </dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Alamat</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pembimbing->alamat }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Professional Information -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-briefcase mr-2 text-green-600"></i>
                        Informasi Profesional
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Jabatan</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pembimbing->jabatan }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Unit Kerja</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pembimbing->departemen }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Peserta yang Dibimbing -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-users mr-2 text-purple-600"></i>
                        Peserta yang Dibimbing
                        <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            {{ $pembimbing->peserta->count() }} Peserta
                        </span>
                    </h3>
                </div>
                <div class="px-6 py-4">
                    @if($pembimbing->peserta->count() > 0)
                        <div class="space-y-3">
                            @foreach($pembimbing->peserta as $peserta)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full text-white flex items-center justify-center font-semibold text-sm">
                                            {{ strtoupper(substr($peserta->nama_lengkap, 0, 2)) }}
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $peserta->nama_lengkap }}</div>
                                            <div class="text-sm text-gray-500">{{ $peserta->nim }} - {{ $peserta->jurusan }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $peserta->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($peserta->status ?? 'aktif') }}
                                        </span>
                                        <a href="{{ route('admin.peserta.show', $peserta) }}" 
                                           class="text-blue-600 hover:text-blue-900 transition-colors duration-200" 
                                           title="Lihat Detail">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-user-graduate text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500 text-sm">Belum ada peserta yang dibimbing oleh pembimbing ini.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Account Information -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-cog mr-2 text-gray-600"></i>
                        Informasi Akun
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Dibuat pada</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $pembimbing->created_at->format('d M Y, H:i') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Terakhir diupdate</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $pembimbing->updated_at->format('d M Y, H:i') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Role</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $pembimbing->user->getRoleDisplayName() }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
