@extends('layouts.pembimbing')

@section('title', 'Daftar Kehadiran')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 mb-2">Daftar Kehadiran Peserta</h1>
                <p class="text-sm text-gray-600">Monitor presensi peserta yang menjadi bimbingan Anda</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500">{{ now()->format('l, d F Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Peserta</label>
                <select name="peserta" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Peserta</option>
                    @if(isset($pesertaList))
                        @foreach($pesertaList as $p)
                            <option value="{{ $p->id }}" {{ request('peserta') == $p->id ? 'selected' : '' }}>
                                {{ $p->nama_lengkap }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai', now()->startOfMonth()->format('Y-m-d')) }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir', now()->format('Y-m-d')) }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Presensi</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="terlambat" {{ request('status') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                    <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="tidak_hadir" {{ request('status') == 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 md:px-6 md:py-2 rounded-lg hover:bg-blue-700 text-sm md:text-base">
                    <i class="fas fa-search mr-1 md:mr-2"></i><span class="hidden sm:inline">Filter</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Quick Overview - Today's Attendance -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Presensi Hari Ini</h2>
            <span class="text-sm text-gray-500">{{ now()->format('d F Y') }}</span>
        </div>
        
        @if(isset($presensiHariIni) && $presensiHariIni->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($presensiHariIni as $presensi)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full text-white flex items-center justify-center font-semibold text-sm">
                                {{ strtoupper(substr($presensi->peserta->nama_lengkap, 0, 2)) }}
                            </div>
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900">{{ $presensi->peserta->nama_lengkap }}</h3>
                                <div class="flex items-center space-x-2 mt-1">
                                    @if($presensi->status == 'hadir')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>{{ $presensi->jam_masuk ? 'Masuk ' . substr($presensi->jam_masuk, 0, 5) : 'Hadir' }}
                                        </span>
                                        @if($presensi->jam_keluar)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-sign-out-alt mr-1"></i>Keluar {{ substr($presensi->jam_keluar, 0, 5) }}
                                            </span>
                                        @endif
                                    @elseif($presensi->status == 'terlambat')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <i class="fas fa-clock mr-1"></i>Terlambat {{ substr($presensi->jam_masuk, 0, 5) }}
                                        </span>
                                        @if($presensi->jam_keluar)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-sign-out-alt mr-1"></i>Keluar {{ substr($presensi->jam_keluar, 0, 5) }}
                                            </span>
                                        @endif
                                    @elseif($presensi->status == 'izin')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-calendar-times mr-1"></i>Izin
                                        </span>
                                    @elseif($presensi->status == 'sakit')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-thermometer mr-1"></i>Sakit
                                        </span>
                                    @elseif($presensi->status == 'alpa')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times mr-1"></i>Alpa
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-calendar-times text-gray-400 text-3xl mb-3"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Presensi Hari Ini</h3>
                <p class="text-gray-600">Peserta belum melakukan presensi untuk hari ini</p>
            </div>
        @endif
    </div>

    <!-- Detailed Attendance List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Riwayat Kehadiran</h2>
            <div class="flex items-center space-x-2">
                <!-- Export Buttons -->
                <button onclick="exportExcel()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </button>
                <button onclick="exportPdf()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </button>
            </div>
        </div>
        
        @if(isset($presensiList) && $presensiList->count() > 0)
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peserta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Keluar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($presensiList as $presensi)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $presensi->tanggal->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full text-white flex items-center justify-center font-semibold text-xs">
                                            {{ strtoupper(substr($presensi->peserta->nama_lengkap, 0, 2)) }}
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $presensi->peserta->nama_lengkap }}</div>
                                            <div class="text-sm text-gray-500">{{ $presensi->peserta->nim }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($presensi->status == 'hadir')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>Hadir
                                        </span>
                                    @elseif($presensi->status == 'terlambat')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <i class="fas fa-clock mr-1"></i>Terlambat
                                        </span>
                                    @elseif($presensi->status == 'izin')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-calendar-times mr-1"></i>Izin
                                        </span>
                                    @elseif($presensi->status == 'sakit')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-thermometer mr-1"></i>Sakit
                                        </span>
                                    @elseif($presensi->status == 'alpa')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times mr-1"></i>Alpa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times mr-1"></i>Tidak Hadir
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $presensi->jam_masuk ? substr($presensi->jam_masuk, 0, 5) : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $presensi->jam_keluar ? substr($presensi->jam_keluar, 0, 5) : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $presensi->lokasi->nama_lokasi ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('pembimbing.kehadiran.show', $presensi) }}" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                            <i class="fa not fa-expand"></i>
                                        </a>
                                        @if($presensi->foto_masuk || $presensi->foto_keluar)
                                            <a href="{{ route('pembimbing.kehadiran.show', $presensi) }}#foto" class="text-green-600 hover:text-green-900" title="Lihat Foto">
                                                <i class="fas fa-images"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden space-y-4">
                @foreach($presensiList as $presensi)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full text-white flex items-center justify-center font-semibold">
                                    {{ strtoupper(substr($presensi->peserta->nama_lengkap, 0, 2)) }}
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ $presensi->peserta->nama_lengkap }}</h3>
                                    <p class="text-sm text-gray-500">{{ $presensi->tanggal->format('d M Y') }}</p>
                                </div>
                            </div>
                            @if($presensi->status == 'hadir')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>Hadir
                                </span>
                            @elseif($presensi->status == 'terlambat')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    <i class="fas fa-clock mr-1"></i>Terlambat
                                </span>
                            @elseif($presensi->status == 'izin')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-calendar-times mr-1"></i>Izin
                                </span>
                            @elseif($presensi->status == 'sakit')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-thermometer mr-1"></i>Sakit
                                </span>
                            @elseif($presensi->status == 'alpa')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times mr-1"></i>Alpa
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-question mr-1"></i>Tidak Diketahui
                                </span>
                            @endif
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                            <div>
                                <span class="text-gray-500">Jam Masuk:</span>
                                <p class="text-gray-900">{{ $presensi->jam_masuk ? substr($presensi->jam_masuk, 0, 5) : '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Jam Keluar:</span>
                                <p class="text-gray-900">{{ $presensi->jam_keluar ? substr($presensi->jam_keluar, 0, 5) : '-' }}</p>
                            </div>
                        </div>
                        
                        <div class="text-sm mb-3">
                            <span class="text-gray-500">Lokasi:</span>
                            <p class="text-gray-900">{{ $presensi->lokasi->nama_lokasi ?? '-' }}</p>
                        </div>
                        
                        <div class="flex items-center space-x-3 pt-3 border-t border-gray-200">
                            <a href="{{ route('pembimbing.kehadiran.show', $presensi) }}" class="flex-1 bg-blue-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-blue-700 text-center">
                                <i class="fas fa-eye mr-1"></i>Detail
                            </a>
                            @if($presensi->foto_masuk || $presensi->foto_keluar)
                                <a href="{{ route('pembimbing.kehadiran.show', $presensi) }}#foto" class="flex-1 bg-green-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-green-700 text-center">
                                    <i class="fas fa-camera mr-1"></i>Foto
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if(isset($presensiList) && $presensiList->hasPages())
                <div class="mt-6">
                    {{ $presensiList->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-8">
                <i class="fas fa-calendar-alt text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Data Presensi</h3>
                <p class="text-gray-600">Belum ada data presensi untuk filter yang dipilih</p>
            </div>
        @endif
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Smooth image hover effects */
    .photo-hover {
        transition: transform 0.2s ease-in-out;
    }
    
    .photo-hover:hover {
        transform: scale(1.02);
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .space-y-6 > * + * {
            margin-top: 1.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Export Excel function
    function exportExcel() {
        const params = new URLSearchParams();
        
        // Get current filter values
        const peserta = document.querySelector('select[name="peserta"]').value;
        const tanggalMulai = document.querySelector('input[name="tanggal_mulai"]').value;
        const tanggalAkhir = document.querySelector('input[name="tanggal_akhir"]').value;
        const status = document.querySelector('select[name="status"]').value;
        
        if (peserta) params.append('peserta', peserta);
        if (tanggalMulai) params.append('tanggal_mulai', tanggalMulai);
        if (tanggalAkhir) params.append('tanggal_akhir', tanggalAkhir);
        if (status) params.append('status', status);
        
        const url = `{{ route('pembimbing.kehadiran.export.excel') }}?${params.toString()}`;
        window.open(url, '_blank');
    }

    // Export PDF function
    function exportPdf() {
        const params = new URLSearchParams();
        
        // Get current filter values
        const peserta = document.querySelector('select[name="peserta"]').value;
        const tanggalMulai = document.querySelector('input[name="tanggal_mulai"]').value;
        const tanggalAkhir = document.querySelector('input[name="tanggal_akhir"]').value;
        const status = document.querySelector('select[name="status"]').value;
        
        if (peserta) params.append('peserta', peserta);
        if (tanggalMulai) params.append('tanggal_mulai', tanggalMulai);
        if (tanggalAkhir) params.append('tanggal_akhir', tanggalAkhir);
        if (status) params.append('status', status);
        
        const url = `{{ route('pembimbing.kehadiran.export.pdf') }}?${params.toString()}`;
        window.open(url, '_blank');
    }

    // Auto submit form on filter change
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.querySelector('form[method="GET"]');
        if (filterForm) {
            const selects = filterForm.querySelectorAll('select, input[type="date"]');
            
            selects.forEach(select => {
                select.addEventListener('change', function() {
                    filterForm.submit();
                });
            });
        }
    });
</script>
@endpush