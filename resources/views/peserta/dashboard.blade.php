@extends('layouts.peserta')

@section('title', 'Home | Presensi STI')

@section('content')
<div class="space-y-6 p-4">
    <!-- Welcome Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center space-x-4">
            <!-- Profile Picture -->
            <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/avatars/' . Auth::user()->avatar) }}" alt="Profile" class="w-full h-full object-cover">
                @else
                    <i class="fas fa-user text-gray-500"></i>
                @endif
            </div>
            
            <!-- User Info -->
            <div class="flex-1">
                <h2 class="text-lg font-semibold text-gray-900">{{ Auth::user()->peserta?->nama_lengkap ?? Auth::user()->name }}</h2>
                <p class="text-sm text-gray-500">Peserta Magang</p>
            </div>
        </div>
    </div>

    <!-- Jadwal Kerja Hari Ini -->
    @if($jamKerja)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-clock text-blue-600 mr-2"></i>
            Jadwal Anda Hari Ini
        </h3>
        
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
            <!-- Jam Masuk -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-sign-in-alt text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($jamKerja->jam_masuk)->format('H:i') }}</p>
                    <p class="text-xs text-gray-500">Masuk</p>
                </div>
            </div>
            
            <!-- Divider -->
            <div class="flex-1 flex items-center justify-center">
                <div class="flex space-x-1">
                    <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                    <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                    <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                </div>
            </div>
            
            <!-- Jam Keluar -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-sign-out-alt text-red-600"></i>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($jamKerja->jam_keluar)->format('H:i') }}</p>
                    <p class="text-xs text-gray-500">Keluar</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Menu Cepat -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Menu Cepat</h3>
        <div class="grid grid-cols-2 gap-4">
            <a href="{{ route('peserta.presensi.index') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg border border-blue-200 hover:bg-blue-100 transition-colors duration-200">
                <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center mb-3">
                    <i class="fas fa-calendar-check text-white"></i>
                </div>
                <span class="font-medium text-blue-900 text-sm">Presensi</span>
                <span class="text-blue-600 text-xs mt-1">Check-in/out</span>
            </a>
            
            <a href="{{ route('peserta.riwayat.index') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg border border-green-200 hover:bg-green-100 transition-colors duration-200">
                <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center mb-3">
                    <i class="fas fa-history text-white"></i>
                </div>
                <span class="font-medium text-green-900 text-sm">Riwayat</span>
                <span class="text-green-600 text-xs mt-1">Lihat data</span>
            </a>
            
            <a href="{{ route('peserta.izin.index') }}" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg border border-orange-200 hover:bg-orange-100 transition-colors duration-200">
                <div class="w-12 h-12 bg-orange-600 rounded-full flex items-center justify-center mb-3">
                    <i class="fas fa-file-medical text-white"></i>
                </div>
                <span class="font-medium text-orange-900 text-sm">Izin/Sakit</span>
                <span class="text-orange-600 text-xs mt-1">Ajukan</span>
            </a>
            
            <a href="{{ route('peserta.kegiatan.index') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg border border-purple-200 hover:bg-purple-100 transition-colors duration-200">
                <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center mb-3">
                    <i class="fas fa-tasks text-white"></i>
                </div>
                <span class="font-medium text-purple-900 text-sm">Kegiatan</span>
                <span class="text-purple-600 text-xs mt-1">Log harian</span>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Real-time clock
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        
        const clockElement = document.getElementById('realTimeClock');
        if (clockElement) {
            clockElement.textContent = timeString;
        }
    }

    // Update every second
    setInterval(updateClock, 1000);
    updateClock(); // Initial call
</script>
@endpush
