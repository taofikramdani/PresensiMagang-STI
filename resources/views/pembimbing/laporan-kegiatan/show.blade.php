@extends('layouts.pembimbing')

@section('title', 'Detail Laporan Kegiatan - ' . $peserta->user->name)

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="space-y-6 p-4">
    <!-- Header & Peserta Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('pembimbing.laporan-kegiatan.index') }}" 
                   class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Detail Laporan Kegiatan</h1>
                    <p class="text-sm text-gray-600">Aktivitas dan perkembangan peserta</p>
                </div>
            </div>
        </div>

        <!-- Peserta Information -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center space-x-4">
                <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center">
                    <span class="text-lg font-medium text-blue-600">
                        {{ strtoupper(substr($peserta->user->name, 0, 2)) }}
                    </span>
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $peserta->nama_lengkap }}</h2>
                    <p class="text-sm text-gray-600">{{ $peserta->user->email }}</p>
                    <div class="flex items-center space-x-4 mt-1">
                        @if($peserta->nim)
                            <span class="text-sm text-gray-500">NIM: {{ $peserta->nim }}</span>
                        @endif
                        @if($peserta->universitas)
                            <span class="text-sm text-gray-500">{{ $peserta->universitas }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Kegiatan List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Kegiatan</h2>
        </div>
        
        <!-- Filter -->
        <form method="GET" action="{{ route('pembimbing.laporan-kegiatan.show', $peserta->id) }}" class="mb-6">
            <div class="flex flex-col lg:flex-row gap-3">
                <!-- Search Input (Full width on mobile, flex-1 on desktop) -->
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kegiatan..." 
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- Compact Date Range -->
                <div class="flex space-x-2">
                    <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" 
                           class="w-32 px-2 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           title="Tanggal Mulai">
                    <span class="flex items-center text-gray-400 text-sm">-</span>
                    <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}" 
                           class="w-32 px-2 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           title="Tanggal Selesai">
                </div>
                
                <!-- Category Select -->
                <div>
                    <select name="kategori" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Kategori</option>
                        <option value="meeting" {{ request('kategori') == 'meeting' ? 'selected' : '' }}>Meeting</option>
                        <option value="pengerjaan_tugas" {{ request('kategori') == 'pengerjaan_tugas' ? 'selected' : '' }}>Pengerjaan Tugas</option>
                        <option value="dokumentasi" {{ request('kategori') == 'dokumentasi' ? 'selected' : '' }}>Dokumentasi</option>
                        <option value="laporan" {{ request('kategori') == 'laporan' ? 'selected' : '' }}>Laporan</option>
                    </select>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm transition-colors duration-200 whitespace-nowrap">
                        <i class="fas fa-search mr-1"></i>Filter
                    </button>
                </div>
            </div>
        </form>

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
                <tbody class="bg-white divide-y divide-gray-200">
                    @php 
                        $groupedKegiatans = $kegiatanList->groupBy(function($kegiatan) {
                            return \Carbon\Carbon::parse($kegiatan->tanggal)->format('Y-m-d');
                        })->sortKeys();
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
                            
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $kegiatan->judul }}</div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $kegiatan->formatted_kategori_aktivitas }}
                                </span>
                            </td>
                            
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 text-justify">
                                    {{ Str::limit($kegiatan->deskripsi,1000) }}
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
                        <td colspan="6" class="px-6 py-8 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-clipboard-list text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Kegiatan</h3>
                                <p class="text-gray-600">Peserta belum menambahkan kegiatan atau sesuai filter.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-6">
            @php 
                $groupedKegiatansMobile = $kegiatanList->groupBy(function($kegiatan) {
                    return \Carbon\Carbon::parse($kegiatan->tanggal)->format('Y-m-d');
                })->sortKeys();
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
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h4 class="font-medium text-gray-900">{{ $kegiatan->judul }}</h4>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $kegiatan->formatted_kategori_aktivitas }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
                                    <i class="fas fa-clock"></i>
                                    <span>
                                        {{ $kegiatan->formatted_jam_mulai }}
                                        @if($kegiatan->jam_selesai)
                                            - {{ $kegiatan->formatted_jam_selesai }}
                                        @endif
                                    </span>
                                    @if($kegiatan->duration)
                                        <span class="text-xs text-gray-400">({{ $kegiatan->duration }})</span>
                                    @endif
                                </div>
                            </div>
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
                                <span class="text-gray-600">{{ $kegiatan->bukti_file_name }}</span>
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            @empty
            <div class="text-center py-8">
                <i class="fas fa-clipboard-list text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Kegiatan</h3>
                <p class="text-gray-600">Peserta belum menambahkan kegiatan atau sesuai filter.</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($kegiatanList->hasPages())
        <div class="mt-6">
            {{ $kegiatanList->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function exportData() {
    const params = new URLSearchParams();
    const tanggalMulai = document.querySelector('input[name="tanggal_mulai"]').value;
    const tanggalSelesai = document.querySelector('input[name="tanggal_selesai"]').value;
    
    if (tanggalMulai) params.append('tanggal_mulai', tanggalMulai);
    if (tanggalSelesai) params.append('tanggal_selesai', tanggalSelesai);
    
    fetch(`{{ route('pembimbing.laporan-kegiatan.export', $peserta->id) }}?${params.toString()}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            downloadExcel(data.data, data.data.peserta.nama);
        } else {
            alert('Gagal export data: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat export data.');
    });
}

function downloadExcel(data, filename) {
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "Tanggal,Jam Mulai,Jam Selesai,Durasi,Judul,Kategori,Deskripsi,Bukti\n";
    
    data.kegiatan.forEach(function(kegiatan) {
        const row = [
            kegiatan.tanggal,
            kegiatan.jam_mulai,
            kegiatan.jam_selesai,
            kegiatan.durasi,
            `"${kegiatan.judul}"`,
            kegiatan.kategori,
            `"${kegiatan.deskripsi}"`,
            kegiatan.bukti
        ].join(",");
        csvContent += row + "\n";
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `laporan_kegiatan_${filename}_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function viewDetail(id) {
    // For now, just show a simple alert. In future, we can fetch detailed data via AJAX
    alert('Detail kegiatan ID: ' + id + '\n\nFitur detail akan dikembangkan lebih lanjut.');
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}
</script>
@endsection