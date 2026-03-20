@extends('layouts.peserta')

@section('title', 'Home | Presensi STI')

@section('content')
<div class="space-y-6 p-4">
    <!-- Welcome Section -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
        <div class="flex items-start space-x-4 mb-4">
            <!-- Profile Picture -->
            <div class="w-16 h-16 bg-gradient-to-br from-gray-300 to-gray-400 rounded-full flex items-center justify-center overflow-hidden flex-shrink-0 shadow-sm">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/avatars/' . Auth::user()->avatar) }}" alt="Profile" class="w-full h-full object-cover">
                @else
                    <i class="fas fa-user text-2xl text-gray-600"></i>
                @endif
            </div>
            
            <!-- User Info -->
            <div class="flex-1">
                <h2 class="text-xl font-semibold text-gray-900 mb-0.5">{{ Auth::user()->peserta?->nama_lengkap ?? Auth::user()->name }}</h2>
                <p class="text-sm text-gray-500">Peserta Magang</p>
            </div>
        </div>

        <!-- Status Presensi -->
        @if($presensiHariIni && $presensiHariIni->jam_masuk)
            <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 flex items-center space-x-3">
                <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check text-white text-xs"></i>
                </div>
                <span class="text-sm font-medium text-green-800">
                    Sudah Presensi | Jam {{ \Carbon\Carbon::parse($presensiHariIni->jam_masuk)->format('H:i') }}
                </span>
            </div>
        @else
            <div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 flex items-center space-x-3">
                <div class="w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-clock text-white text-xs"></i>
                </div>
                <span class="text-sm font-medium text-gray-600">
                    Belum Presensi Hari Ini
                </span>
            </div>
        @endif
    </div>

    <!-- Jadwal dan Lokasi Hari Ini -->
    @if($jamKerja)
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-5">Jadwal dan Lokasi Hari Ini</h3>
        
        <!-- Lokasi Info -->
        @if($lokasi)
        <div class="mb-6 space-y-3">
            <div class="flex items-start space-x-2.5 text-sm text-gray-600 gap-4">
                <i class="fas fa-map-marker-alt text-green-500 mt-0.5"></i>
                <span>Lokasi Presensi : <span class="font-medium text-gray-800">{{ $lokasi->nama_lokasi }}</span></span>
            </div>
            <div class="flex items-center space-x-2.5 gap-2">
                <div class="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0 ">
                    <i class="fas fa-check text-white text-xs"></i>
                </div>
                <span class="text-sm font-medium text-green-700">Dalam area presensi</span>
            </div>
        </div>
        @endif
        
        <!-- Jadwal Waktu -->
        <div class="bg-white border border-gray-300 rounded-xl p-4 mb-4">
            <div class="grid grid-cols-2 gap-4">
                <!-- Jam Masuk -->
                <div class="pr-4 border-r border-gray-300">
                    <div class="flex items-center space-x-2 mb-1.5">
                        <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-arrow-right text-white" style="font-size: 10px;"></i>
                        </div>
                        <p class="text-base font-bold text-gray-900 leading-none">{{ \Carbon\Carbon::parse($jamKerja->jam_masuk)->format('H:i') }}</p>
                        <span class="text-xs text-gray-600 font-normal">Masuk</span>
                    </div>
                    <p class="text-xs text-gray-500 pl-8">Tepat waktu</p>
                </div>
                
                <!-- Jam Keluar -->
                <div class="pl-0">
                    <div class="flex items-center space-x-2">
                        <div class="w-6 h-6 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-arrow-left text-white" style="font-size: 10px;"></i>
                        </div>
                        <p class="text-base font-bold text-gray-900 leading-none">{{ \Carbon\Carbon::parse($jamKerja->jam_keluar)->format('H:i') }}</p>
                        <span class="text-xs text-gray-600 font-normal">Keluar</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Warning Text -->
        <p class="text-xs text-gray-500 mb-6 pl-1">Presensi terlambat setelah {{ \Carbon\Carbon::parse($jamKerja->jam_masuk)->addMinutes(30)->format('H:i') }}</p>
        
        <!-- Tombol Presensi -->
        @if(!$presensiHariIni || !$presensiHariIni->jam_masuk)
        <a href="{{ route('peserta.presensi.index') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-center py-4 rounded-xl transition-colors duration-200 flex items-center justify-center space-x-2">
            <i class="fas fa-calendar-check text-base"></i>
            <span class="text-sm">PRESENSI SEKARANG</span>
        </a>
        @else
        <div class="block w-full bg-gray-300 text-gray-600 font-semibold text-center py-4 rounded-xl flex items-center justify-center space-x-2">
            <i class="fas fa-check-circle text-base"></i>
            <span class="text-sm">SUDAH PRESENSI</span>
        </div>
        @endif
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
