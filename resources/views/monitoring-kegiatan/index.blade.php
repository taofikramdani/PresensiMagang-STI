@extends('layouts.main')

@section('title', 'Monitoring Kegiatan - Day-In')

@section('content')
<div class="space-y-6 p-4">
    <!-- Page Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 mb-2">Monitoring Kegiatan</h1>
                <p class="text-sm text-gray-600">Monitor dan analisis kegiatan peserta magang secara global</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-8 mt-4 sm:mt-0">
                <button onclick="exportData('excel')" class="bg-green-600 text-white px-6 py-2.5 rounded-lg hover:bg-green-700 text-sm font-medium transition-colors duration-200 shadow-sm">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </button>
                <button onclick="exportData('pdf')" class="bg-red-600 text-white px-6 py-2.5 rounded-lg hover:bg-red-700 text-sm font-medium transition-colors duration-200 shadow-sm">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Filter & Pencarian</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                <input type="date" id="tanggal_mulai" value="{{ $tanggal_mulai }}" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                <input type="date" id="tanggal_akhir" value="{{ $tanggal_akhir }}" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                <select id="lokasi_filter" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Lokasi</option>
                    @foreach($lokasiList as $lokasi)
                        <option value="{{ $lokasi->id }}" {{ request('lokasi_id') == $lokasi->id ? 'selected' : '' }}>
                            {{ $lokasi->nama_lokasi }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pembimbing</label>
                <select id="pembimbing_filter" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Pembimbing</option>
                    @foreach($pembimbingList as $pembimbing)
                        <option value="{{ $pembimbing->user_id }}" {{ request('pembimbing_id') == $pembimbing->user_id ? 'selected' : '' }}>
                            {{ $pembimbing->nama_lengkap }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Peserta</label>
                <select id="peserta_filter" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Peserta</option>
                    @foreach($pesertaList as $peserta)
                        <option value="{{ $peserta->id }}" {{ request('peserta_id') == $peserta->id ? 'selected' : '' }}>{{ $peserta->nama_lengkap }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex justify-end">
            <button onclick="applyFilters()" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors duration-200">
                <i class="fas fa-search mr-2"></i>Filter Data
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-tasks text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Kegiatan</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $statistics['total_kegiatan'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-day text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Kegiatan Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $statistics['kegiatan_hari_ini'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-week text-purple-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Kegiatan Minggu Ini</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $statistics['kegiatan_minggu_ini'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-orange-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Peserta Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $statistics['peserta_aktif'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Activity Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-line mr-2 text-blue-600"></i>
                Tren Kegiatan Harian
            </h3>
            <div class="h-80">
                <canvas id="dailyActivityChart"></canvas>
            </div>
        </div>

        <!-- Category Activity Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-pie mr-2 text-green-600"></i>
                Distribusi Kategori Aktivitas
            </h3>
            <div class="h-80">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activities Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-list mr-2 text-purple-600"></i>
                Kegiatan Terbaru
            </h3>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Waktu
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Peserta & Lokasi
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pembimbing
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kegiatan & Kategori
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Deskripsi
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
                            Bukti
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="kegiatanTableBody">
                    @php 
                        $groupedKegiatans = $recentActivities->groupBy(function($kegiatan) {
                            return \Carbon\Carbon::parse($kegiatan->tanggal)->format('Y-m-d');
                        })->sortKeysDesc();
                    @endphp
                    
                    @forelse($groupedKegiatans as $tanggal => $kegiatanGroup)
                        @foreach($kegiatanGroup as $index => $kegiatan)
                        <tr class="hover:bg-gray-50 {{ $index > 0 ? 'border-t-0' : '' }}">
                            <!-- Tanggal - hanya tampil di row pertama -->
                            @if($index === 0)
                            <td class="px-6 py-4 whitespace-nowrap border-r text-center" rowspan="{{ $kegiatanGroup->count() }}">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ \Carbon\Carbon::parse($kegiatan->tanggal)->format('d M Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($kegiatan->tanggal)->locale('id')->isoFormat('dddd') }}
                                </div>
                            </td>
                            @endif
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $kegiatan->formatted_jam_mulai }}
                                    @if($kegiatan->jam_selesai)
                                        - {{ $kegiatan->formatted_jam_selesai }}
                                    @endif
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-blue-600">
                                            {{ strtoupper(substr($kegiatan->peserta->nama_lengkap, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $kegiatan->peserta->nama_lengkap }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $kegiatan->peserta->nim }}
                                            @if($kegiatan->peserta->lokasi)
                                                • {{ $kegiatan->peserta->lokasi->nama_lokasi }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($kegiatan->pembimbingDetail && $kegiatan->pembimbingDetail->nama_lengkap)
                                    {{ $kegiatan->pembimbingDetail->nama_lengkap }}
                                @elseif($kegiatan->pembimbing && $kegiatan->pembimbing->name)
                                    {{ $kegiatan->pembimbing->name }}
                                @elseif($kegiatan->peserta->pembimbingDetail)
                                    {{ $kegiatan->peserta->pembimbingDetail->nama_lengkap }}
                                @elseif($kegiatan->peserta->pembimbing)
                                    {{ $kegiatan->peserta->pembimbing->name }}
                                @else
                                    <span class="text-gray-400">Belum ditentukan</span>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $kegiatan->judul }}</div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $kegiatan->formatted_kategori_aktivitas }}
                                </span>
                            </td>
                            
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 text-justify">
                                    {{ Str::limit($kegiatan->deskripsi, 100) }}
                                </div>
                            </td>
                            
                            <td class="px-2 py-4 whitespace-nowrap w-24">
                                @if($kegiatan->bukti)
                                    <div class="flex items-center">
                                        <a href="{{ asset('storage/' . $kegiatan->bukti) }}" 
                                            target="_blank" 
                                            class="flex items-center text-blue-600 hover:underline" title="{{ $kegiatan->bukti_file_name }}">
                                            @if($kegiatan->is_bukti_image)
                                                <i class="fas fa-file-image text-blue-600 text-lg"></i>
                                            @elseif($kegiatan->bukti_file_type == 'pdf')
                                                <i class="fas fa-file-pdf text-red-600 text-lg"></i>
                                            @else
                                                <i class="fas fa-file-word text-blue-600 text-lg"></i>
                                            @endif
                                        </a>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-clipboard-list text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Kegiatan</h3>
                                <p class="text-gray-600">Belum ada kegiatan yang sesuai dengan filter yang dipilih.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-6 p-4">
            @php 
                $groupedKegiatansMobile = $recentActivities->groupBy(function($kegiatan) {
                    return \Carbon\Carbon::parse($kegiatan->tanggal)->format('Y-m-d');
                })->sortKeysDesc();
            @endphp
            
            @forelse($groupedKegiatansMobile as $tanggal => $kegiatanGroup)
                <!-- Header Tanggal -->
                <div class="bg-gray-50 rounded-lg p-3 border-l-4 border-blue-500">
                    <h3 class="font-semibold text-gray-900">
                        {{ \Carbon\Carbon::parse($kegiatanGroup->first()->tanggal)->format('d M Y') }}
                    </h3>
                    <p class="text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($kegiatanGroup->first()->tanggal)->locale('id')->isoFormat('dddd') }}
                        • {{ $kegiatanGroup->count() }} kegiatan
                    </p>
                </div>
                
                <!-- Kegiatan Cards -->
                <div class="space-y-3 ml-4">
                    @foreach($kegiatanGroup as $kegiatan)
                    <div class="border border-gray-200 rounded-lg p-4 bg-white">
                        <div class="flex items-start space-x-3">
                            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-sm font-medium text-blue-600">
                                    {{ strtoupper(substr($kegiatan->peserta->nama_lengkap, 0, 2)) }}
                                </span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h4 class="font-medium text-gray-900">{{ $kegiatan->judul }}</h4>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $kegiatan->formatted_kategori_aktivitas }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600 mb-2">
                                    <div>{{ $kegiatan->peserta->nama_lengkap }} ({{ $kegiatan->peserta->nim }})</div>
                                    @if($kegiatan->peserta->lokasi)
                                        <div>📍 {{ $kegiatan->peserta->lokasi->nama_lokasi }}</div>
                                    @endif
                                </div>
                                <div class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
                                    <i class="fas fa-clock"></i>
                                    <span>
                                        {{ $kegiatan->formatted_jam_mulai }}
                                        @if($kegiatan->jam_selesai)
                                            - {{ $kegiatan->formatted_jam_selesai }}
                                        @endif
                                    </span>
                                </div>
                                <div class="text-sm text-gray-700 mb-3">{{ Str::limit($kegiatan->deskripsi, 100) }}</div>
                                
                                @if($kegiatan->bukti)
                                    <div class="flex items-center space-x-2 text-sm">
                                        @if($kegiatan->is_bukti_image)
                                            <i class="fas fa-file-image text-blue-600"></i>
                                        @elseif($kegiatan->bukti_file_type == 'pdf')
                                            <i class="fas fa-file-pdf text-red-600"></i>
                                        @else
                                            <i class="fas fa-file-word text-blue-600"></i>
                                        @endif
                                        <a href="{{ asset('storage/' . $kegiatan->bukti) }}" target="_blank" class="text-blue-600 hover:underline">
                                            Lihat Bukti
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @empty
            <div class="text-center py-8">
                <i class="fas fa-clipboard-list text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Kegiatan</h3>
                <p class="text-gray-600">Belum ada kegiatan yang sesuai dengan filter yang dipilih.</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($recentActivities->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $recentActivities->links() }}
        </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    setDefaultDates();
});

function setDefaultDates() {
    const today = new Date();
    const lastWeek = new Date(today.getTime() - (7 * 24 * 60 * 60 * 1000));
    
    document.getElementById('tanggal_akhir').value = today.toISOString().split('T')[0];
    document.getElementById('tanggal_mulai').value = lastWeek.toISOString().split('T')[0];
}

function initializeCharts() {
    // Daily Activity Chart
    const dailyCtx = document.getElementById('dailyActivityChart').getContext('2d');
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyChartData['labels']) !!},
            datasets: [{
                label: 'Jumlah Kegiatan',
                data: {!! json_encode($dailyChartData['data']) !!},
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($categoryChartData['labels']) !!},
            datasets: [{
                data: {!! json_encode($categoryChartData['data']) !!},
                backgroundColor: [
                    '#3B82F6',
                    '#10B981',
                    '#F59E0B',
                    '#EF4444',
                    '#8B5CF6',
                    '#F97316'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function applyFilters() {
    const filters = {
        tanggal_mulai: document.getElementById('tanggal_mulai').value,
        tanggal_akhir: document.getElementById('tanggal_akhir').value,
        lokasi_id: document.getElementById('lokasi_filter').value,
        pembimbing_id: document.getElementById('pembimbing_filter').value,
        peserta_id: document.getElementById('peserta_filter').value
    };

    // Build query string
    const queryParams = new URLSearchParams();
    Object.keys(filters).forEach(key => {
        if (filters[key]) {
            queryParams.append(key, filters[key]);
        }
    });

    // Reload page with filters
    window.location.href = `{{ route('admin.monitoring-kegiatan.index') }}?${queryParams.toString()}`;
}

function exportData(format) {
    console.log('Export triggered for format:', format);
    
    // Get filter values from inputs or server variables  
    const tanggal_mulai = document.getElementById('tanggal_mulai').value || '{{ request("tanggal_mulai", now()->subDays(7)->format("Y-m-d")) }}';
    const tanggal_akhir = document.getElementById('tanggal_akhir').value || '{{ request("tanggal_akhir", now()->format("Y-m-d")) }}';
    const lokasi_id = document.getElementById('lokasi_filter').value || '{{ request("lokasi_id") }}';
    const pembimbing_id = document.getElementById('pembimbing_filter').value || '{{ request("pembimbing_id") }}';
    const peserta_id = document.getElementById('peserta_filter').value || '{{ request("peserta_id") }}';

    console.log('Filter parameters:', { tanggal_mulai, tanggal_akhir, lokasi_id, pembimbing_id, peserta_id });

    // Build URL parameters
    const params = new URLSearchParams();
    if (tanggal_mulai) params.append('tanggal_mulai', tanggal_mulai);
    if (tanggal_akhir) params.append('tanggal_akhir', tanggal_akhir);
    if (lokasi_id) params.append('lokasi_id', lokasi_id);
    if (pembimbing_id) params.append('pembimbing_id', pembimbing_id);
    if (peserta_id) params.append('peserta_id', peserta_id);

    let url;
    let filename;
    
    if (format === 'excel') {
        url = `{{ route('admin.monitoring-kegiatan.export-excel') }}?${params.toString()}`;
        filename = `monitoring_kegiatan_${tanggal_mulai}_${tanggal_akhir}.xlsx`;
    } else if (format === 'pdf') {
        url = `{{ route('admin.monitoring-kegiatan.export-pdf') }}?${params.toString()}`;
        filename = `monitoring_kegiatan_${tanggal_mulai}_${tanggal_akhir}.pdf`;
    }

    console.log('Export URL:', url);

    if (format === 'excel') {
        // For Excel, create download link
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } else {
        // For PDF, use direct window.location
        window.location.href = url;
    }
}
</script>
@endsection