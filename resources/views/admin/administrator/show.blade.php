@extends('layouts.main')

@section('title', 'Detail Administrator - Day-In')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Administrator</h1>
                <p class="text-gray-600 mt-1">Informasi lengkap administrator: {{ $administrator->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.administrator.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
                @if($user->isAdmin())
                <a href="{{ route('admin.administrator.edit', $administrator) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors duration-200">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Profile Card -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Profil Administrator</h2>
        </div>
        
        <div class="p-6">
            <div class="flex items-start space-x-6">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    <div class="h-20 w-20 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-user-shield text-blue-600 text-2xl"></i>
                    </div>
                </div>
                
                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-3 mb-2">
                        <h3 class="text-xl font-bold text-gray-900">{{ $administrator->name }}</h3>
                        @if($administrator->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-circle mr-1 text-green-400"></i>
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-circle mr-1 text-red-400"></i>
                                Nonaktif
                            </span>
                        @endif
                    </div>
                    
                    <div class="space-y-1">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-envelope mr-2"></i>
                            {{ $administrator->email }}
                            @if($administrator->email_verified_at)
                                <span class="text-green-600 ml-1">
                                    <i class="fas fa-check-circle"></i> Terverifikasi
                                </span>
                            @else
                                <span class="text-red-600 ml-1">
                                    <i class="fas fa-times-circle"></i> Belum terverifikasi
                                </span>
                            @endif
                        </p>
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-user-tag mr-2"></i>
                            {{ $administrator->getRoleDisplayName() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Information -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Informasi Akun</h2>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tanggal Bergabung
                    </label>
                    <div class="text-sm text-gray-900">
                        <i class="fas fa-calendar-plus mr-2 text-blue-600"></i>
                        {{ $administrator->created_at->format('d F Y') }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $administrator->created_at->format('H:i') }} WIB
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Terakhir Diperbarui
                    </label>
                    <div class="text-sm text-gray-900">
                        <i class="fas fa-clock mr-2 text-green-600"></i>
                        {{ $administrator->updated_at->format('d F Y') }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $administrator->updated_at->format('H:i') }} WIB
                    </div>
                </div>

                @if($administrator->email_verified_at)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Email Diverifikasi
                    </label>
                    <div class="text-sm text-gray-900">
                        <i class="fas fa-check-circle mr-2 text-green-600"></i>
                        {{ $administrator->email_verified_at->format('d F Y') }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $administrator->email_verified_at->format('H:i') }} WIB
                    </div>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Status Akun
                    </label>
                    <div class="text-sm text-gray-900">
                        @if($administrator->is_active)
                            <i class="fas fa-user-check mr-2 text-green-600"></i>
                            Akun Aktif
                        @else
                            <i class="fas fa-user-times mr-2 text-red-600"></i>
                            Akun Nonaktif
                        @endif
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $administrator->is_active ? 'Dapat mengakses sistem' : 'Tidak dapat mengakses sistem' }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Bergabung Sejak
                    </label>
                    <div class="text-sm text-gray-900">
                        <i class="fas fa-hourglass-start mr-2 text-purple-600"></i>
                        {{ $administrator->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    @if($user->isAdmin())
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Aksi Administrator</h2>
        </div>
        
        <div class="p-6">
            <div class="flex flex-wrap gap-3">
                <!-- Edit Button -->
                <a href="{{ route('admin.administrator.edit', $administrator) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors duration-200">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Data
                </a>

                <!-- Toggle Status Button -->
                @if($administrator->id !== auth()->id())
                <form action="{{ route('admin.administrator.toggle-status', $administrator) }}" 
                      method="POST" 
                      class="inline-block"
                      onsubmit="return confirm('Yakin ingin mengubah status administrator ini?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-md transition-colors duration-200">
                        <i class="fas fa-{{ $administrator->is_active ? 'toggle-off' : 'toggle-on' }} mr-2"></i>
                        {{ $administrator->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>

                <!-- Delete Button -->
                <form action="{{ route('admin.administrator.destroy', $administrator) }}" 
                      method="POST" 
                      class="inline-block"
                      onsubmit="return confirm('Yakin ingin menghapus administrator ini? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md transition-colors duration-200">
                        <i class="fas fa-trash mr-2"></i>
                        Hapus Administrator
                    </button>
                </form>
                @else
                <div class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-600 font-medium rounded-md">
                    <i class="fas fa-info-circle mr-2"></i>
                    Ini adalah akun Anda sendiri
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection