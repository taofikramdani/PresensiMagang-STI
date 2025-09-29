@extends('layouts.main')

@section('title', 'Dashboard - Presensi Magang')

@section('content')
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <!-- Dashboard Header -->
    <div class="text-black px-6 py-4 border-b border-gray-200">
        <h1 class="text-xl font-semibold flex items-center">
            <i class="fas fa-tachometer-alt mr-2 text-blue-600"></i>
            Dashboard 
        </h1>
    </div>

    <!-- Stats Cards -->
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Peserta -->
            <div class="bg-blue-500 text-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 flex justify-between items-center">
                    <div>
                        <div class="text-2xl font-bold">{{ $totalPeserta }}</div>
                        <div class="text-sm opacity-90">Total Peserta Terdaftar</div>
                    </div>
                    <div class="text-white">
                        <i class="fas fa-users text-3xl opacity-80"></i>
                    </div>
                </div>
            </div>

            <!-- Total Pembimbing -->
            <div class="bg-green-500 text-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 flex justify-between items-center">
                    <div>
                        <div class="text-2xl font-bold">{{ $totalPembimbing }}</div>
                        <div class="text-sm opacity-90">Total Pembimbing</div>
                    </div>
                    <div class="text-white">
                        <i class="fas fa-chalkboard-teacher text-3xl opacity-80"></i>
                    </div>
                </div>
            </div>

            <!-- Presensi Hari Ini -->
            <div class="bg-amber-500 text-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 flex justify-between items-center">
                    <div>
                        <div class="text-2xl font-bold">{{ $totalPresensiHariIni }}</div>
                        <div class="text-sm opacity-90">Presensi Hari Ini</div>
                    </div>
                    <div class="text-white">
                        <i class="fas fa-calendar-check text-3xl opacity-80"></i>
                    </div>
                </div>
            </div>

            <!-- Izin Pending -->
            <div class="bg-red-500 text-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 flex justify-between items-center">
                    <div>
                        <div class="text-2xl font-bold">{{ $totalIzinPending }}</div>
                        <div class="text-sm opacity-90">Izin Pending</div>
                    </div>
                    <div class="text-white">
                        <i class="fas fa-clock text-3xl opacity-80"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Area -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Weekly Attendance Chart -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Presensi 7 Hari Terakhir</h3>
                    <div class="h-64">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Status Distribution Chart -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Distribusi Status Presensi</h3>
                    <div class="h-64">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Peserta per Pembimbing Chart -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Peserta per Pembimbing</h3>
                    <div class="h-64">
                        <canvas id="pembimbingChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Monthly Overview Chart -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Overview Presensi Bulanan {{ date('Y') }}</h3>
                    <div class="h-64">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Statistics -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <!-- Kampus/Universitas Distribution -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Distribusi Universitas/Sekolah
                    </h3>
                    <div class="h-64">
                        <canvas id="kampusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Periode Magang Statistics -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Periode Magang
                    </h3>
                    <div class="h-64">
                        <canvas id="periodeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Lokasi Magang Distribution -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Distribusi Lokasi Magang
                    </h3>
                    <div class="h-64">
                        <canvas id="lokasiChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script>
    // Weekly Attendance Chart
    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    new Chart(weeklyCtx, {
        type: 'line',
        data: {
            labels: @json($weeklyLabels),
            datasets: [{
                label: 'Kehadiran',
                data: @json($weeklyAttendance),
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
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa'],
            datasets: [{
                data: [
                    {{ $statusDistribution['hadir'] }},
                    {{ $statusDistribution['terlambat'] }},
                    {{ $statusDistribution['izin'] }},
                    {{ $statusDistribution['sakit'] }},
                    {{ $statusDistribution['alpa'] }}
                ],
                backgroundColor: [
                    '#10b981',
                    '#f59e0b',
                    '#3b82f6',
                    '#8b5cf6',
                    '#ef4444'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Peserta per Pembimbing Chart
    const pembimbingCtx = document.getElementById('pembimbingChart').getContext('2d');
    new Chart(pembimbingCtx, {
        type: 'bar',
        data: {
            labels: @json($pembimbingNames),
            datasets: [{
                label: 'Jumlah Peserta',
                data: @json($pembimbingPesertaCounts),
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: 'rgb(34, 197, 94)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Monthly Overview Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: @json($monthlyLabels),
            datasets: [{
                label: 'Total Kehadiran',
                data: @json($monthlyData),
                backgroundColor: 'rgba(168, 85, 247, 0.8)',
                borderColor: 'rgb(168, 85, 247)',
                borderWidth: 1
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

    // Kampus Distribution Chart
    const kampusCtx = document.getElementById('kampusChart').getContext('2d');
    new Chart(kampusCtx, {
        type: 'pie',
        data: {
            labels: @json($kampusNames),
            datasets: [{
                data: @json($kampusCounts),
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                    '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#6366f1',
                    '#14b8a6', '#eab308', '#dc2626', '#9333ea', '#0891b2'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: { size: 11 },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const dataset = data.datasets[0];
                                    const value = dataset.data[i];
                                    return {
                                        text: `${label} (${value})`,
                                        fillStyle: dataset.backgroundColor[i],
                                        strokeStyle: dataset.borderColor,
                                        lineWidth: dataset.borderWidth,
                                        hidden: isNaN(dataset.data[i]),
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    enabled: true
                },
                datalabels: {
                    color: '#fff',
                    font: {
                        size: 11,
                        weight: 'bold'
                    },
                    formatter: (value, ctx) => {
                        const total = ctx.chart.data.datasets[0].data
                            .reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return percentage + '%';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    // Periode Magang Chart
    const periodeCtx = document.getElementById('periodeChart').getContext('2d');
    new Chart(periodeCtx, {
        type: 'doughnut',
        data: {
            labels: @json($periodeLabels),
            datasets: [{
                data: @json($periodeCounts),
                backgroundColor: [
                    '#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#84cc16'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11
                        },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const dataset = data.datasets[0];
                                    const value = dataset.data[i];
                                    return {
                                        text: `${label} (${value})`,
                                        fillStyle: dataset.backgroundColor[i],
                                        strokeStyle: dataset.borderColor,
                                        lineWidth: dataset.borderWidth,
                                        hidden: isNaN(dataset.data[i]),
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} peserta (${percentage}%)`;
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: {
                        size: 11,
                        weight: 'bold'
                    },
                    formatter: (value, ctx) => {
                        const total = ctx.chart.data.datasets[0].data
                            .reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return percentage + '%';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    // Lokasi Magang Chart
    const lokasiCtx = document.getElementById('lokasiChart').getContext('2d');
    new Chart(lokasiCtx, {
        type: 'bar',
        data: {
            labels: @json($lokasiNames),
            datasets: [{
                label: 'Jumlah Peserta',
                data: @json($lokasiCounts),
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: 'rgb(239, 68, 68)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.parsed.x} peserta`;
                        }
                    }
                }
            }
        }
    });
</script>
@endsection
