@extends('layouts.main')

@section('title', 'Kehadiran - Day-In')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Kehadiran</h1>
            <p class="text-gray-600 mt-1">Kelola data kehadiran peserta magang</p>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <!-- Export Buttons Group -->
            <div class="flex flex-col sm:flex-row gap-2">
                <button onclick="exportExcel()" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                    <i class="fas fa-file-excel mr-2"></i>
                    Export Excel
                </button>
                <button onclick="exportPdf()" class="inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Export PDF
                </button>
            </div>
            
            <!-- Separator -->
            <div class="hidden sm:block w-px h-8 bg-gray-300"></div>
            
            <!-- Input Manual Button -->
            <button onclick="openInputModal()" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                <i class="fas fa-plus mr-2"></i>
                Input Manual
            </button>
        </div>
    </div>

    <!-- Daily Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-blue-600">Total Peserta</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $dailyStats['total_peserta'] }}</p>
                </div>
                </div>
            </div>
            
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-600">Hadir</p>
                        <p class="text-2xl font-bold text-green-900">{{ $dailyStats['hadir'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-yellow-600 text-2xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-600">Izin/Sakit</p>
                        <p class="text-2xl font-bold text-yellow-900">{{ $dailyStats['izin'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-600">Alpa</p>
                        <p class="text-2xl font-bold text-red-900">{{ $dailyStats['alpa'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Working Day Info -->
    @if(!$dailyStats['is_working_day'])
    <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-yellow-600 text-xl"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-yellow-700">
                    Hari ini adalah hari libur. 
                </p>
            </div>
        </div>
    </div>
    @else
    <div class="p-4 bg-green-50 border-l-4 border-green-400">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-calendar-check text-green-600 text-xl"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-700">
                    Hari kerja - {{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                </p>
                @if($dailyStats['active_schedules']->count() > 0)
                <p class="text-xs text-green-600 mt-1">
                    Jadwal aktif: 
                    @foreach($dailyStats['active_schedules'] as $schedule)
                        <span class="inline-block">{{ $schedule['nama_shift'] }} ({{ implode(', ', $schedule['hari_kerja']) }})</span>
                        @if(!$loop->last), @endif
                    @endforeach
                </p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Working Schedule Info (Collapsible) -->
    @if($dailyStats['active_schedules']->count() > 0)
    <div class="bg-gray-50 border-b border-gray-200">
        <div class="p-4">
            <button type="button" onclick="toggleScheduleInfo()" class="flex items-center text-sm font-medium text-gray-600 hover:text-gray-800">
                <i class="fas fa-clock mr-2"></i>
                <span>Jadwal Kerja Aktif</span>
                <i id="schedule-chevron" class="fas fa-chevron-down ml-2 transform transition-transform duration-200"></i>
            </button>
            <div id="schedule-info" class="hidden mt-3">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($dailyStats['active_schedules'] as $schedule)
                <div class="bg-white p-3 rounded-lg border border-gray-200">
                    <h4 class="font-medium text-gray-800">{{ $schedule['nama_shift'] }}</h4>
                    <p class="text-sm text-gray-600 mt-1">{{ $schedule['jam_kerja'] }}</p>
                    <p class="text-xs text-gray-500 mt-2">
                        Hari: {{ implode(', ', $schedule['hari_kerja']) }}
                    </p>
                </div>
                @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <h2 class="text-lg font-medium text-gray-900">Data Kehadiran</h2>
                
                <!-- Search and Filter -->
                <form method="GET" action="{{ route('admin.kehadiran.index') }}" class="flex flex-col sm:flex-row gap-3">
                    <input type="date" name="tanggal" 
                           value="{{ $tanggal }}"
                           class="border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" 
                               value="{{ $search }}"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                               placeholder="Cari peserta...">
                    </div>
                    
                    <select name="status" class="border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="hadir" {{ $status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="terlambat" {{ $status == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="izin" {{ $status == 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="sakit" {{ $status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="alpa" {{ $status == 'alpa' ? 'selected' : '' }}>Alpa</option>
                    </select>
                    
                    <select name="pembimbing_id" class="border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Pembimbing</option>
                        @foreach($pembimbingList as $pembimbing)
                            <option value="{{ $pembimbing->user_id }}" 
                                    {{ $pembimbing_id == $pembimbing->user_id ? 'selected' : '' }}>
                                {{ $pembimbing->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                    
                    <select name="lokasi_id" class="border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Lokasi</option>
                        @foreach($lokasiList as $lokasi)
                            <option value="{{ $lokasi->id }}" 
                                    {{ $lokasi_id == $lokasi->id ? 'selected' : '' }}>
                                {{ $lokasi->nama_lokasi }}
                            </option>
                        @endforeach
                    </select>
                    
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium transition-colors">
                        Filter
                    </button>
                </form>
            </div>
        </div>

        @if($presensiRecords->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">
                                Nama Peserta
                            </th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 hidden lg:table-cell">
                                Lokasi
                            </th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 hidden md:table-cell">
                                Pembimbing
                            </th>
                            <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">
                                Jam Masuk/Keluar
                            </th>
                            <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">
                                Status
                            </th>
                            <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($presensiRecords as $presensi)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-blue-600">
                                                    {{ substr($presensi->peserta->nama_lengkap, 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $presensi->peserta->nama_lengkap }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $presensi->peserta->nim ?? 'No NIM' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden lg:table-cell whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ $presensi->lokasi->nama_lokasi ?? 'Belum ditentukan' }}
                                </td>
                                <td class="hidden md:table-cell whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ $presensi->peserta->pembimbingDetail->nama_lengkap ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                                    <div class="space-y-1">
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($presensi->tanggal)->format('d/m/Y') }}
                                        </div>
                                        @if($presensi->jam_masuk)
                                            <div class="text-green-600">
                                                <i class="fas fa-sign-in-alt mr-1"></i>
                                                {{ $presensi->jam_masuk }}
                                            </div>
                                        @endif
                                        @if($presensi->jam_keluar)
                                            <div class="text-orange-600">
                                                <i class="fas fa-sign-out-alt mr-1"></i>
                                                {{ $presensi->jam_keluar }}
                                            </div>
                                        @elseif($presensi->jam_masuk)
                                            <div class="text-xs text-gray-400">Belum pulang</div>
                                        @endif
                                        @if(!$presensi->jam_masuk)
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-center">
                                    @php
                                        $statusConfig = [
                                            'hadir' => ['bg-green-100 text-green-800', 'fas fa-check-circle'],
                                            'terlambat' => ['bg-yellow-100 text-yellow-800', 'fas fa-clock'],
                                            'izin' => ['bg-blue-100 text-blue-800', 'fas fa-calendar-check'],
                                            'sakit' => ['bg-purple-100 text-purple-800', 'fas fa-user-injured'],
                                            'alpa' => ['bg-red-100 text-red-800', 'fas fa-times-circle']
                                        ];
                                        $config = $statusConfig[$presensi->status] ?? ['bg-gray-100 text-gray-800', 'fas fa-question'];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config[0] }}">
                                        <i class="{{ $config[1] }} mr-1"></i>
                                        {{ ucfirst($presensi->status) }}
                                    </span>
                                    @if($presensi->keterangan)
                                        <div class="text-xs text-gray-500 mt-1 truncate max-w-20" title="{{ $presensi->keterangan }}">
                                            {{ $presensi->keterangan }}
                                        </div>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button onclick="editPresensi({{ $presensi->id }})" 
                                                class="text-blue-600 hover:text-blue-900 transition-colors" title="Edit Presensi">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="viewDetail({{ $presensi->id }})" 
                                                class="text-green-600 hover:text-green-900 transition-colors" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-gray-500">
                                    <i class="fas fa-clipboard-check text-6xl text-gray-300 mb-4"></i>
                                    <p class="text-lg font-medium mb-2">Belum ada data presensi</p>
                                    <p class="text-sm">Data presensi untuk tanggal ini belum tersedia</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-clipboard-check text-6xl text-gray-300 mb-4"></i>
                <p class="text-lg font-medium text-gray-500 mb-2">Belum ada data presensi</p>
                <p class="text-sm text-gray-400">Data presensi untuk filter ini belum tersedia</p>
            </div>
        @endif

        <!-- Pagination -->
        @if($presensiRecords->hasPages())
            <div class="mt-6">
                {{ $presensiRecords->links() }}
            </div>
        @endif
    </div>

        <!-- Peserta Alpa Alert -->
        @if($pesertaAlpa->count() > 0)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                    <h3 class="text-sm font-medium text-red-800">Peserta yang belum presensi hari ini:</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($pesertaAlpa as $peserta)
                        <div class="flex items-center justify-between bg-white p-2 rounded border">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $peserta->nama_lengkap }}</p>
                                <p class="text-xs text-gray-500">{{ $peserta->pembimbingDetail->nama_lengkap ?? '-' }}</p>
                            </div>
                            <button onclick="inputPresensiAlpa({{ $peserta->id }})" 
                                    class="text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700">
                                Input
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
</div>

<!-- Modal Input/Edit Presensi -->
<div id="presensiModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl border-2 border-gray-200 max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Input Presensi Manual</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="presensiForm" class="space-y-5">
                <input type="hidden" id="presensi_id" name="presensi_id">
                
                <!-- Peserta Selection -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Peserta
                        <span class="text-red-500">*</span>
                    </label>
                    <select id="peserta_id" name="peserta_id" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors">
                        <option value="">Pilih Peserta</option>
                    </select>
                    <div id="peserta_error" class="hidden text-red-500 text-xs mt-1"></div>
                </div>
                
                <!-- Location Info -->
                <div id="lokasi_info" class="hidden"></div>
                
                <!-- Date Selection -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tanggal
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="tanggal" name="tanggal" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors">
                    <div id="tanggal_error" class="hidden text-red-500 text-xs mt-1"></div>
                </div>
                
                <!-- Status Selection -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Status
                        <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status" required onchange="toggleTimeFields()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors">
                        <option value="">Pilih Status</option>
                        <option value="hadir">Hadir</option>
                        <option value="terlambat">Terlambat</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="alpa">Alpa</option>
                    </select>
                    <div id="status_error" class="hidden text-red-500 text-xs mt-1"></div>
                </div>
                
                <!-- Time Fields (shown for hadir/terlambat) -->
                <div id="timeFields" class="hidden">
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <h4 class="text-sm font-medium text-blue-800 mb-3">
                            Waktu Kehadiran
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-sign-in-alt mr-2 text-green-600"></i>Jam Masuk
                                    <span class="text-red-500">*</span>
                                </label>
                                <input type="time" id="jam_masuk" name="jam_masuk"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors">
                                <div id="jam_masuk_error" class="hidden text-red-500 text-xs mt-1"></div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-sign-out-alt mr-2 text-red-600"></i>Jam Keluar
                                </label>
                                <input type="time" id="jam_keluar" name="jam_keluar"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors">
                                <div id="jam_keluar_error" class="hidden text-red-500 text-xs mt-1"></div>
                                <div class="text-xs text-gray-500 mt-1">Kosongkan jika belum pulang</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Keterangan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Keterangan
                        <span class="text-gray-500 text-xs">(Opsional)</span>
                    </label>
                    <textarea id="keterangan" name="keterangan" rows="3" 
                              placeholder="Masukkan keterangan tambahan jika diperlukan..."
                              maxlength="255"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm resize-none transition-colors"></textarea>
                    <div class="text-xs text-gray-500 mt-1">Maksimal 255 karakter</div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal()" 
                            class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit" id="submitBtn"
                            class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium transition-colors">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let pesertaList = [];
let isEditMode = false;

// Load peserta list on page load
document.addEventListener('DOMContentLoaded', function() {
    // Test SweetAlert loading
    console.log('DOM loaded, testing SweetAlert...');
    if (typeof Swal !== 'undefined') {
        console.log('SweetAlert is available');
        // Test SweetAlert
        // Swal.fire('Test', 'SweetAlert is working!', 'success');
    } else {
        console.error('SweetAlert is not available');
    }
    
    loadPesertaList();
    
    // Set default date to today
    document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];
});

// Load peserta list for dropdown
function loadPesertaList() {
    console.log('Loading peserta list...');
    fetch('/kehadiran/create')
        .then(response => {
            console.log('Peserta response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Peserta data received:', data);
            pesertaList = data.peserta;
            updatePesertaDropdown();
        })
        .catch(error => {
            console.error('Error loading peserta:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Gagal memuat data peserta: ' + error.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
}

// Update peserta dropdown
function updatePesertaDropdown() {
    const select = document.getElementById('peserta_id');
    select.innerHTML = '<option value="">Pilih Peserta</option>';
    
    pesertaList.forEach(peserta => {
        const option = document.createElement('option');
        option.value = peserta.id;
        option.dataset.lokasiId = peserta.lokasi_id || '';
        option.dataset.lokasiNama = peserta.lokasi ? peserta.lokasi.nama_lokasi : 'Belum ditentukan';
        
        const pembimbingName = peserta.pembimbing_detail ? peserta.pembimbing_detail.nama_lengkap : 'No Pembimbing';
        const lokasiName = peserta.lokasi ? peserta.lokasi.nama_lokasi : 'Lokasi belum ditentukan';
        option.textContent = `${peserta.nama_lengkap} - ${pembimbingName} (${lokasiName})`;
        select.appendChild(option);
    });
    
    // Add event listener to show location info when peserta is selected
    select.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        updateLokasiInfo(selectedOption);
    });
}

// Update location info display
function updateLokasiInfo(selectedOption) {
    const lokasiInfoDiv = document.getElementById('lokasi_info');
    if (!lokasiInfoDiv) return;
    
    if (selectedOption.value) {
        const lokasiNama = selectedOption.dataset.lokasiNama;
        lokasiInfoDiv.innerHTML = `
            <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                <div class="flex items-center">
                    <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                    <span class="text-sm font-medium text-blue-800">Lokasi Presensi:</span>
                    <span class="text-sm text-blue-700 ml-2">${lokasiNama}</span>
                </div>
            </div>
        `;
        lokasiInfoDiv.classList.remove('hidden');
    } else {
        lokasiInfoDiv.classList.add('hidden');
    }
}

// Open input modal
function openInputModal() {
    isEditMode = false;
    document.getElementById('modalTitle').textContent = 'Input Presensi Manual';
    document.getElementById('submitBtn').textContent = 'Simpan';
    document.getElementById('presensiForm').reset();
    document.getElementById('presensi_id').value = '';
    document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];
    document.getElementById('timeFields').classList.add('hidden');
    document.getElementById('lokasi_info').classList.add('hidden');
    document.getElementById('presensiModal').classList.remove('hidden');
}

// Open edit modal
function editPresensi(id) {
    isEditMode = true;
    document.getElementById('modalTitle').textContent = 'Edit Presensi';
    document.getElementById('submitBtn').textContent = 'Update';
    
    fetch(`/kehadiran/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            const presensi = data.presensi;
            document.getElementById('presensi_id').value = presensi.id;
            document.getElementById('peserta_id').value = presensi.peserta_id;
            document.getElementById('tanggal').value = presensi.tanggal;
            document.getElementById('status').value = presensi.status;
            document.getElementById('jam_masuk').value = presensi.jam_masuk || '';
            document.getElementById('jam_keluar').value = presensi.jam_keluar || '';
            document.getElementById('keterangan').value = presensi.keterangan || '';
            
            toggleTimeFields();
            document.getElementById('presensiModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error loading presensi:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Gagal memuat data presensi',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
}

// Input presensi for alpa students
function inputPresensiAlpa(pesertaId) {
    openInputModal();
    document.getElementById('peserta_id').value = pesertaId;
    document.getElementById('status').value = 'alpa';
    
    // Update location info for selected peserta
    const selectElement = document.getElementById('peserta_id');
    const selectedOption = selectElement.querySelector(`option[value="${pesertaId}"]`);
    if (selectedOption) {
        updateLokasiInfo(selectedOption);
    }
}

// Close modal
function closeModal() {
    document.getElementById('presensiModal').classList.add('hidden');
}

// Toggle time fields based on status
function toggleTimeFields() {
    const status = document.getElementById('status').value;
    const timeFields = document.getElementById('timeFields');
    const jamMasuk = document.getElementById('jam_masuk');
    
    // Clear any previous error states
    clearFieldError('status');
    
    if (status === 'hadir' || status === 'terlambat') {
        timeFields.classList.remove('hidden');
        jamMasuk.required = true;
        
        // Set default time if empty
        if (!jamMasuk.value) {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            jamMasuk.value = `${hours}:${minutes}`;
        }
    } else {
        timeFields.classList.add('hidden');
        jamMasuk.required = false;
        jamMasuk.value = '';
        document.getElementById('jam_keluar').value = '';
        clearFieldError('jam_masuk');
        clearFieldError('jam_keluar');
    }
}

// Clear field error
function clearFieldError(fieldName) {
    const errorDiv = document.getElementById(`${fieldName}_error`);
    const field = document.getElementById(fieldName);
    if (errorDiv) {
        errorDiv.classList.add('hidden');
        errorDiv.textContent = '';
    }
    if (field) {
        field.classList.remove('border-red-300');
        field.classList.add('border-gray-300');
    }
}

// Show field validation error
function showFieldError(fieldId, message) {
    const errorDiv = document.getElementById(fieldId + '_error');
    const field = document.getElementById(fieldId);
    
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.classList.remove('hidden');
    }
    
    if (field) {
        field.classList.add('border-red-300');
        field.classList.remove('border-gray-300');
    }
}

// Validate form before submission
function validateForm() {
    const pesertaId = document.getElementById('peserta_id').value;
    const tanggal = document.getElementById('tanggal').value;
    const status = document.getElementById('status').value;
    const jamMasuk = document.getElementById('jam_masuk').value;
    const jamKeluar = document.getElementById('jam_keluar').value;
    
    let isValid = true;
    
    // Clear previous errors
    clearAllErrors();
    
    // Validate peserta
    if (!pesertaId) {
        showFieldError('peserta_id', 'Pilih peserta terlebih dahulu');
        isValid = false;
    }
    
    // Validate tanggal
    if (!tanggal) {
        showFieldError('tanggal', 'Tanggal harus diisi');
        isValid = false;
    } else {
        // Check if date is not in the future
        const selectedDate = new Date(tanggal);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate > today) {
            showFieldError('tanggal', 'Tanggal tidak boleh lebih dari hari ini');
            isValid = false;
        }
    }
    
    // Validate status
    if (!status) {
        showFieldError('status', 'Pilih status kehadiran');
        isValid = false;
    }
    
    // Validate jam masuk for hadir/terlambat
    if ((status === 'hadir' || status === 'terlambat') && !jamMasuk) {
        showFieldError('jam_masuk', 'Jam masuk harus diisi untuk status ini');
        isValid = false;
    }
    
    // Validate jam keluar tidak boleh sebelum jam masuk
    if (jamMasuk && jamKeluar && jamKeluar < jamMasuk) {
        showFieldError('jam_keluar', 'Jam keluar tidak boleh sebelum jam masuk');
        isValid = false;
    }
    
    return isValid;
}

// Clear all validation errors
function clearAllErrors() {
    const errorElements = document.querySelectorAll('[id$="_error"]');
    const fieldElements = document.querySelectorAll('#presensiForm input, #presensiForm select, #presensiForm textarea');
    
    errorElements.forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
    });
    
    fieldElements.forEach(el => {
        el.classList.remove('border-red-300');
        el.classList.add('border-gray-300');
    });
}

// Show field error
function showFieldError(fieldName, message) {
    const errorDiv = document.getElementById(`${fieldName}_error`);
    const field = document.getElementById(fieldName);
    if (errorDiv) {
        errorDiv.classList.remove('hidden');
        errorDiv.textContent = message;
    }
    if (field) {
        field.classList.remove('border-gray-300');
        field.classList.add('border-red-300');
    }
}

// Validate form
function validateForm() {
    let isValid = true;
    
    // Clear all previous errors
    ['peserta', 'tanggal', 'status', 'jam_masuk', 'jam_keluar'].forEach(field => {
        clearFieldError(field);
    });
    
    // Validate peserta
    const pesertaId = document.getElementById('peserta_id').value;
    if (!pesertaId) {
        showFieldError('peserta', 'Peserta harus dipilih');
        isValid = false;
    }
    
    // Validate tanggal
    const tanggal = document.getElementById('tanggal').value;
    if (!tanggal) {
        showFieldError('tanggal', 'Tanggal harus diisi');
        isValid = false;
    }
    
    // Validate status
    const status = document.getElementById('status').value;
    if (!status) {
        showFieldError('status', 'Status harus dipilih');
        isValid = false;
    }
    
    // Validate jam_masuk for hadir/terlambat
    if ((status === 'hadir' || status === 'terlambat')) {
        const jamMasuk = document.getElementById('jam_masuk').value;
        if (!jamMasuk) {
            showFieldError('jam_masuk', 'Jam masuk harus diisi');
            isValid = false;
        }
        
        // Validate jam_keluar if provided
        const jamKeluar = document.getElementById('jam_keluar').value;
        if (jamMasuk && jamKeluar && jamKeluar <= jamMasuk) {
            showFieldError('jam_keluar', 'Jam keluar harus lebih besar dari jam masuk');
            isValid = false;
        }
    }
    
    return isValid;
}

// Handle form submission
document.getElementById('presensiForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate form first
    if (!validateForm()) {
        return;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Debug: log data yang akan dikirim
    console.log('Data yang akan dikirim:', data);
    
    const url = isEditMode ? `/kehadiran/${data.presensi_id}` : '/kehadiran';
    const method = isEditMode ? 'PUT' : 'POST';
    
    // Prepare request data
    const requestData = {
        peserta_id: data.peserta_id,
        tanggal: data.tanggal,
        status: data.status,
        jam_masuk: data.jam_masuk || null,
        jam_keluar: data.jam_keluar || null,
        keterangan: data.keterangan || null,
        _token: '{{ csrf_token() }}'
    };
    
    if (isEditMode) {
        requestData._method = 'PUT';
    }
    
    console.log('Request data:', requestData);
    console.log('URL:', url);
    console.log('Method:', method);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Response bukan JSON:', text);
                throw new Error('Response tidak valid: ' + text);
            }
        });
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            Swal.fire({
                title: 'Berhasil!',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                closeModal();
                location.reload(); // Refresh to show updated data
            });
        } else if (data.error) {
            Swal.fire({
                title: 'Error!',
                text: data.error,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else if (data.errors) {
            // Handle validation errors
            let errorMessage = 'Validation errors:\n';
            for (let field in data.errors) {
                errorMessage += `${field}: ${data.errors[field].join(', ')}\n`;
            }
            Swal.fire({
                title: 'Validation Error!',
                text: errorMessage,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: 'Terjadi kesalahan yang tidak diketahui',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error!',
            text: 'Terjadi kesalahan: ' + error.message,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Export data functions
function exportExcel() {
    console.log('Export Excel triggered');
    
    // Use server values directly to ensure correct filtered data
    const tanggal = '{{ $tanggal }}';
    const pembimbingId = '{{ $pembimbing_id }}';
    const lokasiId = '{{ $lokasi_id }}';
    const status = '{{ $status }}';
    const search = '{{ $search }}';
    
    console.log('Using server filter parameters:', { tanggal, pembimbingId, lokasiId, status, search });
    
    // Build URL with server filter parameters
    const params = new URLSearchParams();
    if (tanggal) params.append('tanggal', tanggal);
    if (pembimbingId) params.append('pembimbing_id', pembimbingId);
    if (lokasiId) params.append('lokasi_id', lokasiId);
    if (status) params.append('status', status);
    if (search) params.append('search', search);
    
    const url = `{{ route('admin.kehadiran.export-excel') }}?${params.toString()}`;
    console.log('Export URL:', url);
    
    // Create temporary link to trigger download
    const link = document.createElement('a');
    link.href = url;
    link.download = `kehadiran_${tanggal || 'semua'}.xlsx`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function exportPdf() {
    console.log('Export PDF triggered');
    
    // Use server values directly to ensure correct filtered data
    const tanggal = '{{ $tanggal }}';
    const pembimbingId = '{{ $pembimbing_id }}';
    const lokasiId = '{{ $lokasi_id }}';
    const status = '{{ $status }}';
    const search = '{{ $search }}';
    
    console.log('Using server filter parameters:', { tanggal, pembimbingId, lokasiId, status, search });
    
    // Build URL with server filter parameters
    const params = new URLSearchParams();
    if (tanggal) params.append('tanggal', tanggal);
    if (pembimbingId) params.append('pembimbing_id', pembimbingId);
    if (lokasiId) params.append('lokasi_id', lokasiId);
    if (status) params.append('status', status);
    if (search) params.append('search', search);
    
    const url = `{{ route('admin.kehadiran.export-pdf') }}?${params.toString()}`;
    console.log('Export URL:', url);
    
    window.location.href = url;
}

// Legacy export function (keep for compatibility)
function exportData() {
    const currentDate = document.querySelector('input[name="tanggal"]').value || new Date().toISOString().split('T')[0];
    window.location.href = `/kehadiran/export?tanggal=${currentDate}&format=excel`;
}

// Test SweetAlert function
function testSweetAlert() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Test Berhasil!',
            text: 'SweetAlert2 bekerja dengan baik',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    } else {
        alert('SweetAlert2 tidak ter-load');
    }
}

// Close modal when clicking outside
document.getElementById('presensiModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Clear all filters and reload page
function clearFilters() {
    // Clear URL parameters and reload
    const baseUrl = window.location.pathname;
    window.location.href = baseUrl;
}

// View detail presensi
function viewDetail(presensiId) {
    window.location.href = `{{ url('/admin/kehadiran') }}/${presensiId}`;
}

// Toggle schedule info
function toggleScheduleInfo() {
    const scheduleInfo = document.getElementById('schedule-info');
    const chevron = document.getElementById('schedule-chevron');
    
    if (scheduleInfo.classList.contains('hidden')) {
        scheduleInfo.classList.remove('hidden');
        chevron.classList.add('rotate-180');
    } else {
        scheduleInfo.classList.add('hidden');
        chevron.classList.remove('rotate-180');
    }
}

// Add escape key functionality
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('presensiModal').classList.contains('hidden')) {
        closeModal();
    }
});
</script>
@endsection
