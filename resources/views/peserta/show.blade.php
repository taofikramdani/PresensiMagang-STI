@extends('layouts.main')

@section('title', 'Detail Peserta')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Peserta Magang</h1>
                <p class="text-gray-600 mt-1">Informasi lengkap peserta magang</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.peserta.edit', $peserta) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-md transition-colors duration-200">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
                <a href="{{ route('admin.peserta.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Profile Card -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center space-x-6 mb-6">
                    <div class="h-20 w-20 bg-blue-500 rounded-full text-white grid place-items-center text-2xl">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $peserta->nama_lengkap }}</h2>
                        <p class="text-gray-600">{{ $peserta->nim }}</p>
                        <div class="flex items-center mt-2">
                            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full 
                                {{ $peserta->status === 'aktif' 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($peserta->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                    <i class="fas fa-user-circle mr-2 text-blue-600"></i>
                    Informasi Personal
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                        <p class="text-gray-900">{{ $peserta->user->email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Username</label>
                        <p class="text-gray-900">{{ $peserta->user->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">No. HP</label>
                        <p class="text-gray-900">{{ $peserta->no_telepon }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $peserta->status === 'aktif' 
                                ? 'bg-green-100 text-green-800' 
                                : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($peserta->status) }}
                        </span>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Alamat</label>
                        <p class="text-gray-900">{{ $peserta->alamat }}</p>
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                    <i class="fas fa-graduation-cap mr-2 text-blue-600"></i>
                    Informasi Akademik
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">NIM/NISN</label>
                        <p class="text-gray-900 font-mono">{{ $peserta->nim }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Jurusan</label>
                        <p class="text-gray-900">{{ $peserta->jurusan }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Universitas/Sekolah</label>
                        <p class="text-gray-900">{{ $peserta->universitas }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Side Information -->
        <div class="space-y-6">
            <!-- Periode Magang -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                    <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                    Periode Magang
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Mulai</label>
                        <p class="text-gray-900 font-medium">
                            {{ \Carbon\Carbon::parse($peserta->tanggal_mulai)->format('d F Y') }}
                        </p>
                        <p class="text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($peserta->tanggal_mulai)->diffForHumans() }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Selesai</label>
                        <p class="text-gray-900 font-medium">
                            {{ \Carbon\Carbon::parse($peserta->tanggal_selesai)->format('d F Y') }}
                        </p>
                        <p class="text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($peserta->tanggal_selesai)->diffForHumans() }}
                        </p>
                    </div>
                    <div class="border-t border-gray-200 pt-4">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Durasi Magang</label>
                        <p class="text-gray-900 font-medium">
                            {{ \Carbon\Carbon::parse($peserta->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($peserta->tanggal_selesai)) }} hari
                        </p>
                    </div>
                </div>
            </div>

            <!-- Pembimbing Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                    <i class="fas fa-user-tie mr-2 text-blue-600"></i>
                    Pembimbing
                </h3>
                @if($peserta->pembimbingDetail)
                    <div class="text-center">
                        <div class="h-16 w-16 bg-green-500 rounded-full text-white grid place-items-center text-xl mx-auto mb-3">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h4 class="font-medium text-gray-900">{{ $peserta->pembimbingDetail->nama_lengkap ?? 'N/A' }}</h4>
                        <div class="mt-3">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                {{ ($peserta->pembimbingDetail->status ?? '') === 'aktif' 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($peserta->pembimbingDetail->status ?? 'tidak aktif') }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="text-center text-gray-500">
                        <div class="h-16 w-16 bg-gray-300 rounded-full text-gray-400 grid place-items-center text-xl mx-auto mb-3">
                            <i class="fas fa-user-times"></i>
                        </div>
                        <p>Belum ada pembimbing</p>
                    </div>
                @endif
            </div>

            <!-- Lokasi Magang Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                    <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
                    Lokasi Magang
                </h3>
                @if($peserta->lokasi)
                    <div class="space-y-3">
                        <div class="text-center">
                            <div class="h-16 w-16 bg-blue-500 rounded-full text-white grid place-items-center text-xl mx-auto mb-3">
                                <i class="fas fa-building"></i>
                            </div>
                            <h4 class="font-medium text-gray-900">{{ $peserta->lokasi->nama_lokasi }}</h4>
                        </div>
                        
                        <div class="pt-3 border-t border-gray-200">
                            <div class="space-y-2 text-sm">
                                <div class="flex items-start">
                                    <i class="fas fa-map-marker-alt mr-2 text-gray-400 mt-0.5"></i>
                                    <span class="text-gray-700">{{ $peserta->lokasi->alamat }}</span>
                                </div>
                                @if($peserta->lokasi->latitude && $peserta->lokasi->longitude)
                                    <div class="flex items-center">
                                        <i class="fas fa-crosshairs mr-2 text-gray-400"></i>
                                        <span class="text-gray-700 font-mono text-xs">
                                            {{ $peserta->lokasi->latitude }}, {{ $peserta->lokasi->longitude }}
                                        </span>
                                    </div>
                                @endif
                                @if($peserta->lokasi->radius)
                                    <div class="flex items-center">
                                        <i class="fas fa-circle-notch mr-2 text-gray-400"></i>
                                        <span class="text-gray-700">Radius: {{ $peserta->lokasi->radius }}m</span>
                                    </div>
                                @endif
                                <div class="flex items-center">
                                    <i class="fas fa-info-circle mr-2 text-gray-400"></i>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $peserta->lokasi->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $peserta->lokasi->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center text-gray-500">
                        <div class="h-16 w-16 bg-gray-300 rounded-full text-gray-400 grid place-items-center text-xl mx-auto mb-3">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <p>Lokasi belum ditentukan</p>
                        <p class="text-xs text-gray-400 mt-1">Hubungi admin untuk menetapkan lokasi magang</p>
                    </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                    <i class="fas fa-cogs mr-2 text-blue-600"></i>
                    Aksi Cepat
                </h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.peserta.edit', $peserta) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Data
                    </a>
                    <button onclick="window.print()" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        <i class="fas fa-print mr-2"></i>
                        Print Detail
                    </button>
                    <form action="{{ route('admin.peserta.destroy', $peserta) }}" 
                          method="POST" 
                          class="w-full"
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus data peserta ini? Data yang dihapus tidak dapat dikembalikan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                            <i class="fas fa-trash mr-2"></i>
                            Hapus Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
    @media print {
        .print\:hidden {
            display: none !important;
        }
    }
</style>
@endsection
