@extends('layouts.pembimbing')

@section('title', 'Dashboard Pembimbing')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    
    .chart-legend {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
    }
    
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 3px;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Peserta -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $totalPeserta ?? 0 }}</h3>
                    <p class="text-sm text-gray-600">Total Peserta</p>
                </div>
            </div>
        </div>

        <!-- Hadir Hari Ini -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $hadirHariIni ?? 0 }}</h3>
                    <p class="text-sm text-gray-600">Hadir Hari Ini</p>
                </div>
            </div>
        </div>

        <!-- Izin Pending -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $izinPending ?? 0 }}</h3>
                    <p class="text-sm text-gray-600">Izin Menunggu</p>
                </div>
            </div>
        </div>

        <!-- Tidak Hadir -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $tidakHadir ?? 0 }}</h3>
                    <p class="text-sm text-gray-600">Tidak Hadir</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart Kehadiran Mingguan -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Kehadiran 7 Hari Terakhir</h2>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-chart-line mr-1"></i>
                    Trend Mingguan
                </div>
            </div>
            <div class="chart-container">
                <canvas id="weeklyAttendanceChart"></canvas>
            </div>
        </div>

        <!-- Chart Status Presensi -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Distribusi Status Presensi</h2>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Bulan Ini
                </div>
            </div>
            <div class="chart-container">
                <canvas id="statusDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart Performa Peserta -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Tingkat Kehadiran per Peserta</h2>
            <div class="text-sm text-gray-500">
                <i class="fas fa-chart-bar mr-1"></i>
                Bulan {{ date('F Y') }}
            </div>
        </div>
        <div class="chart-container" style="height: 400px;">
            <canvas id="pesertaPerformanceChart"></canvas>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Presensi Terbaru -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Presensi Hari Ini</h2>
                <a href="{{ route('pembimbing.kehadiran.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua</a>
            </div>
            
            <div class="space-y-3">
                @forelse($recentPresensi ?? [] as $presensi)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-green-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $presensi->peserta->nama_lengkap ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500">{{ $presensi->created_at->setTimezone('Asia/Jakarta')->format('H:i') }}</p>
                            </div>
                        </div>
                        <span class="text-xs bg-green-100 text-green-600 px-2 py-1 rounded-full">
                            {{ $presensi->jenis ?? 'Masuk' }}
                        </span>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-check text-gray-400 text-2xl mb-2"></i>
                        <p class="text-sm text-gray-500">Belum ada presensi hari ini</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Pengajuan Izin -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Pengajuan Izin Terbaru</h2>
                <a href="{{ route('pembimbing.izin.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua</a>
            </div>
            
            <div class="space-y-3">
                @forelse($recentIzin ?? [] as $izin)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 {{ $izin->status == 'pending' ? 'bg-yellow-100' : ($izin->status == 'disetujui' ? 'bg-green-100' : 'bg-red-100') }} rounded-full flex items-center justify-center">
                                @if($izin->status == 'pending')
                                    <i class="fas fa-clock text-yellow-600 text-xs"></i>
                                @elseif($izin->status == 'disetujui')
                                    <i class="fas fa-check text-green-600 text-xs"></i>
                                @else
                                    <i class="fas fa-times text-red-600 text-xs"></i>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $izin->peserta->nama_lengkap ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500">{{ $izin->jenis }} - {{ $izin->tanggal->format('d M') }}</p>
                            </div>
                        </div>
                        <span class="text-xs {{ $izin->status == 'pending' ? 'bg-yellow-100 text-yellow-600' : ($izin->status == 'disetujui' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600') }} px-2 py-1 rounded-full">
                            {{ ucfirst($izin->status) }}
                        </span>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list text-gray-400 text-2xl mb-2"></i>
                        <p class="text-sm text-gray-500">Belum ada pengajuan izin</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart Colors
    const colors = {
        primary: '#3B82F6',
        secondary: '#10B981', 
        warning: '#F59E0B',
        danger: '#EF4444',
        info: '#06B6D4',
        success: '#22C55E',
        light: '#F8FAFC'
    };

    // Weekly Attendance Chart
    const weeklyCtx = document.getElementById('weeklyAttendanceChart').getContext('2d');
    const weeklyChart = new Chart(weeklyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($weeklyLabels ?? ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']) !!},
            datasets: [{
                label: 'Hadir',
                data: {!! json_encode($weeklyAttendance ?? [12, 15, 10, 18, 14, 8, 5]) !!},
                borderColor: colors.primary,
                backgroundColor: colors.primary + '20',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colors.primary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }, {
                label: 'Terlambat',
                data: {!! json_encode($weeklyTerlambat ?? [2, 3, 1, 4, 2, 1, 0]) !!},
                borderColor: colors.warning,
                backgroundColor: colors.warning + '20',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colors.warning,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#F1F5F9'
                    },
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            elements: {
                point: {
                    hoverRadius: 8
                }
            }
        }
    });

    // Status Distribution Chart (Doughnut)
    const statusCtx = document.getElementById('statusDistributionChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha'],
            datasets: [{
                data: [
                    {{ $statusDistribution['hadir'] ?? 0 }},
                    {{ $statusDistribution['terlambat'] ?? 0 }},
                    {{ $statusDistribution['izin'] ?? 0 }},
                    {{ $statusDistribution['sakit'] ?? 0 }},
                    {{ $statusDistribution['alpa'] ?? 0 }}
                ],
                backgroundColor: [
                    colors.success,
                    colors.warning,
                    colors.info,
                    colors.primary,
                    colors.danger
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });

    // Peserta Performance Chart (Horizontal Bar)
    const performanceCtx = document.getElementById('pesertaPerformanceChart').getContext('2d');
    const performanceChart = new Chart(performanceCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($pesertaNames ?? ['Andi Wijaya', 'Budi Santoso', 'Citra Dewi', 'Doni Pratama', 'Eka Sari']) !!},
            datasets: [{
                label: 'Tingkat Kehadiran (%)',
                data: {!! json_encode($pesertaPerformance ?? [95, 88, 92, 78, 85]) !!},
                backgroundColor: function(context) {
                    const value = context.parsed.y;
                    if (value >= 90) return colors.success;
                    if (value >= 80) return colors.warning;
                    return colors.danger;
                },
                borderColor: function(context) {
                    const value = context.parsed.y;
                    if (value >= 90) return colors.success;
                    if (value >= 80) return colors.warning;
                    return colors.danger;
                },
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: '#F1F5F9'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Auto-refresh dashboard data every 5 minutes
    setInterval(function() {
        console.log('Dashboard data refresh check...');
        // Add AJAX call here if needed
    }, 300000); // 5 minutes
});
</script>
@endpush