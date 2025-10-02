@extends('layouts.peserta')

@section('title', 'Riwayat | Presensi STI')

@push('styles')
<style>
    .status-badge {
        transition: all 0.2s ease;
    }
    
    .presensi-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .presensi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Table responsive styles */
    @media (max-width: 767px) {
        .desktop-table {
            display: none !important;
        }
        
        .mobile-cards {
            display: block !important;
        }
    }
    
    @media (min-width: 768px) {
        .desktop-table {
            display: block !important;
        }
        
        .mobile-cards {
            display: none !important;
        }
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }
        
        .table-responsive table {
            min-width: 700px;
        }
        
        .table-responsive th,
        .table-responsive td {
            font-size: 11px;
            padding: 6px 4px;
        }
        
        .mobile-card {
            display: block;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            padding: 1rem;
            background: white;
        }
        
        .mobile-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .mobile-card .card-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }
        
        .mobile-card .card-item {
            display: flex;
            flex-direction: column;
        }
        
        .mobile-card .card-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }
        
        .mobile-card .card-value {
            font-size: 0.875rem;
            color: #111827;
            font-weight: 500;
        }
    }
    
    /* Hover effects */
    tbody tr:hover {
        background-color: rgb(249 250 251);
    }
    
    .status-badge {
        white-space: nowrap;
    }
</style>
@endpush

@section('content')
<div class="space-y-6 p-4">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 animate-fade-in">
        <div class="mb-4">
            <h1 class="text-xl font-bold text-gray-900 mb-2">Riwayat Presensi</h1>
            <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-chart-bar mr-2"></i>
                    <span>Total Hari Kerja: {{ $statistik['total_hari_kerja'] }} hari</span>
                </div>
                @if($peserta->tanggal_mulai && $peserta->tanggal_selesai)
                <div class="flex items-center text-sm text-blue-600">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <span>Periode Magang: {{ \Carbon\Carbon::parse($peserta->tanggal_mulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($peserta->tanggal_selesai)->format('d/m/Y') }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Filter Bulan dan Export -->
        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
            <form method="GET" class="flex space-x-3 flex-1">
                <div class="flex-1">
                    <input type="month" 
                           name="bulan" 
                           value="{{ $bulan }}"
                           onchange="this.form.submit()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </form>
            
            <!-- Export Buttons -->
            <div class="flex space-x-2">
                <!-- Export PDF Bulanan -->
                <a href="{{ route('peserta.riwayat.export-pdf', ['bulan' => $bulan]) }}" 
                   class="px-3 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition-colors duration-200 flex items-center justify-center"
                   title="Export presensi untuk bulan {{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->locale('id')->isoFormat('MMMM Y') }}">
                    <i class="fas fa-file-pdf mr-2"></i>Export Bulan
                </a>
                
                <!-- Export PDF Periode Magang -->
                <a href="{{ route('peserta.riwayat.export-pdf') }}" 
                   class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition-colors duration-200 flex items-center justify-center"
                   title="Export presensi untuk seluruh periode magang">
                    <i class="fas fa-calendar-alt mr-2"></i>Export Periode
                </a>
            </div>
        </div>
    </div>

    <!-- Statistik Bulan Ini -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-3 animate-fade-in">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-blue-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-base font-bold text-gray-900">{{ $statistik['total_presensi'] ?? 0 }}</h3>
                    <p class="text-xs text-gray-600">Total Presensi</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-base font-bold text-gray-900">{{ $statistik['tepat_waktu'] ?? 0 }}</h3>
                    <p class="text-xs text-gray-600">Tepat Waktu</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-orange-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-base font-bold text-gray-900">{{ $statistik['terlambat'] ?? 0 }}</h3>
                    <p class="text-xs text-gray-600">Terlambat</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-times text-yellow-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-base font-bold text-gray-900">{{ $statistik['izin'] ?? 0 }}</h3>
                    <p class="text-xs text-gray-600">Izin</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-thermometer text-blue-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-base font-bold text-gray-900">{{ $statistik['sakit'] ?? 0 }}</h3>
                    <p class="text-xs text-gray-600">Sakit</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-base font-bold text-gray-900">{{ $statistik['alpha'] ?? 0 }}</h3>
                    <p class="text-xs text-gray-600">Alpa</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Presensi -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 animate-fade-in">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Presensi</h2>
        </div>
        
        @if($riwayatPresensi->count() > 0)
            <!-- Desktop Table View -->
            <div class="desktop-table overflow-x-auto table-responsive">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Keluar</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($riwayatPresensi as $presensi)
                            @php
                                $statusConfig = [
                                    'hadir' => ['icon' => 'fa-check', 'class' => 'bg-green-100 text-green-800'],
                                    'terlambat' => ['icon' => 'fa-clock', 'class' => 'bg-yellow-100 text-yellow-800'],
                                    'izin' => ['icon' => 'fa-file-medical', 'class' => 'bg-blue-100 text-blue-800'],
                                    'alpha' => ['icon' => 'fa-times', 'class' => 'bg-red-100 text-red-800'],
                                    'sakit' => ['icon' => 'fa-thermometer-half', 'class' => 'bg-purple-100 text-purple-800']
                                ];
                                $config = $statusConfig[$presensi->status] ?? $statusConfig['alpha'];
                            @endphp
                            
                            <tr class="hover:bg-gray-50 transition-colors">
                                <!-- Tanggal -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ \Carbon\Carbon::parse($presensi->tanggal)->locale('id')->isoFormat('ddd, D MMM Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($presensi->tanggal)->diffForHumans() }}
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['class'] }}">
                                        <i class="fas {{ $config['icon'] }} mr-1"></i>
                                        {{ ucfirst($presensi->status) }}
                                    </span>
                                </td>
                                
                                <!-- Jam Masuk -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-900">
                                        {{ $presensi->jam_masuk ? \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i') : '-' }}
                                    </div>
                                    @if($presensi->keterlambatan > 0)
                                        <div class="text-xs text-yellow-600">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            +{{ $presensi->getKeterlambatanFormatted() }}
                                        </div>
                                    @endif
                                </td>
                                
                                <!-- Jam Keluar -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-900">
                                        {{ $presensi->jam_keluar ? \Carbon\Carbon::parse($presensi->jam_keluar)->format('H:i') : '-' }}
                                    </div>
                                </td>
                                
                                <!-- Durasi -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-900">
                                        {{ $presensi->durasi_kerja ? $presensi->getDurasiKerjaFormatted() : '-' }}
                                    </div>
                                </td>
                                <!-- Aksi -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="{{ route('peserta.riwayat.detail', $presensi->id) }}" 
                                       class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-eye mr-1"></i>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile Card View -->
            <div class="mobile-cards space-y-4 p-4">
                @foreach($riwayatPresensi as $presensi)
                    @php
                        $statusConfig = [
                            'hadir' => ['icon' => 'fa-check', 'class' => 'bg-green-100 text-green-800'],
                            'terlambat' => ['icon' => 'fa-clock', 'class' => 'bg-yellow-100 text-yellow-800'],
                            'izin' => ['icon' => 'fa-file-medical', 'class' => 'bg-blue-100 text-blue-800'],
                            'alpha' => ['icon' => 'fa-times', 'class' => 'bg-red-100 text-red-800'],
                            'sakit' => ['icon' => 'fa-thermometer-half', 'class' => 'bg-purple-100 text-purple-800']
                        ];
                        $config = $statusConfig[$presensi->status] ?? $statusConfig['alpha'];
                    @endphp
                    
                    <div class="mobile-card">
                        <div class="card-header">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-900">
                                    {{ \Carbon\Carbon::parse($presensi->tanggal)->locale('id')->isoFormat('ddd, D MMM Y') }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($presensi->tanggal)->diffForHumans() }}
                                </span>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['class'] }}">
                                <i class="fas {{ $config['icon'] }} mr-1"></i>
                                {{ ucfirst($presensi->status) }}
                            </span>
                        </div>
                        
                        <div class="card-content">
                            <div class="card-item">
                                <span class="card-label">Jam Masuk</span>
                                <span class="card-value">
                                    {{ $presensi->jam_masuk ? \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i') : '-' }}
                                    @if($presensi->keterlambatan > 0)
                                        <span class="text-xs text-yellow-600 ml-2">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            +{{ $presensi->getKeterlambatanFormatted() }}
                                        </span>
                                    @endif
                                </span>
                            </div>
                            
                            <div class="card-item">
                                <span class="card-label">Jam Keluar</span>
                                <span class="card-value">
                                    {{ $presensi->jam_keluar ? \Carbon\Carbon::parse($presensi->jam_keluar)->format('H:i') : '-' }}
                                </span>
                            </div>
                            
                            <div class="card-item">
                                <span class="card-label">Durasi</span>
                                <span class="card-value">
                                    {{ $presensi->durasi_kerja ? $presensi->getDurasiKerjaFormatted() : '-' }}
                                </span>
                            </div>
                            
                            <div class="card-item">
                                <span class="card-label">Aksi</span>
                                <a href="{{ route('peserta.riwayat.detail', $presensi->id) }}" 
                                   class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700 transition-colors w-fit">
                                    <i class="fas fa-eye mr-1"></i>
                                    Detail
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-calendar-times text-gray-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Data Presensi</h3>
                <p class="text-gray-500 mb-4">Belum ada riwayat presensi untuk bulan yang dipilih.</p>
                <a href="{{ route('peserta.presensi.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Mulai Presensi
                </a>
            </div>
        @endif

        <!-- Pagination -->
        @if($riwayatPresensi->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $riwayatPresensi->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto submit form when month changes
    document.addEventListener('DOMContentLoaded', function() {
        const monthSelect = document.querySelector('select[name="bulan"]');
        if (monthSelect) {
            monthSelect.addEventListener('change', function() {
                this.form.submit();
            });
        }
        
        // Add loading state
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                const button = this.querySelector('button[type="submit"]');
                if (button) {
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
                }
            });
        }
    });
</script>
@endpush
