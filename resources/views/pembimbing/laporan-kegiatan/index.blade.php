@extends('layouts.pembimbing')

@section('title', 'Laporan Kegiatan Peserta')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="space-y-6 p-4">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 mb-2">Laporan Kegiatan Peserta</h1>
                    <p class="text-sm text-gray-600">Monitor aktivitas dan perkembangan peserta bimbingan Anda</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-8 mt-4 sm:mt-0">
                    <button onclick="exportExcel()" type="button"
                        class="bg-green-600 text-white px-6 py-2.5 rounded-lg hover:bg-green-700 text-sm font-medium transition-colors duration-200 shadow-sm">
                        <i class="fas fa-file-excel mr-2"></i>Export Excel
                    </button>
                    <button onclick="exportPdf()" type="button"
                        class="bg-red-600 text-white px-6 py-2.5 rounded-lg hover:bg-red-700 text-sm font-medium transition-colors duration-200 shadow-sm">
                        <i class="fas fa-file-pdf mr-2"></i>Export PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Peserta & Aktivitas</h2>
            </div>

            <form method="GET" action="{{ route('pembimbing.laporan-kegiatan.index') }}" class="mb-6">
                <div class="flex flex-col lg:flex-row gap-3">
                    <!-- Search Input (Full width on mobile, flex-1 on desktop) -->
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari peserta..." 
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
                                Peserta
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Informasi
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aktivitas
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kegiatan Terakhir
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($pesertaList as $peserta)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-blue-600">
                                            {{ strtoupper(substr($peserta->user->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $peserta->nama_lengkap }}</div>
                                        <div class="text-sm text-gray-500">{{ $peserta->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($peserta->nim)
                                        <div>NIM: {{ $peserta->nim }}</div>
                                    @endif
                                    @if($peserta->instansi)
                                        <div class="text-gray-500">{{ $peserta->instansi }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-4">
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-blue-600">{{ $peserta->kegiatans->count() }}</div>
                                        <div class="text-xs text-gray-500">Total Kegiatan</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-green-600">
                                            {{ $peserta->kegiatans->where('tanggal', '>=', \Carbon\Carbon::now()->startOfWeek())->count() }}
                                        </div>
                                        <div class="text-xs text-gray-500">Minggu Ini</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($peserta->kegiatans->isNotEmpty())
                                    @php $lastKegiatan = $peserta->kegiatans->first(); @endphp
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900">{{ $lastKegiatan->judul }}</div>
                                        <div class="text-gray-500">
                                            {{ \Carbon\Carbon::parse($lastKegiatan->tanggal)->format('d M Y') }}
                                            {{ $lastKegiatan->formatted_jam_mulai }}
                                        </div>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $lastKegiatan->formatted_kategori_aktivitas }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm">Belum ada kegiatan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('pembimbing.laporan-kegiatan.show', $peserta->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button onclick="exportPesertaData({{ $peserta->id }})" type="button"
                                            class="text-green-600 hover:text-green-900" title="Export Data">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-users text-4xl mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Peserta</h3>
                                    <p class="text-gray-600">Belum ada peserta yang dibimbing atau sesuai filter.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden space-y-4">
                @forelse($pesertaList as $peserta)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <span class="text-sm font-medium text-blue-600">
                                {{ strtoupper(substr($peserta->user->name, 0, 2)) }}
                            </span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900">{{ $peserta->nama_lengkap }}</h3>
                            <p class="text-sm text-gray-500">{{ $peserta->user->email }}</p>
                            @if($peserta->nim || $peserta->instansi)
                                <div class="text-sm text-gray-600 mt-1">
                                    @if($peserta->nim) NIM: {{ $peserta->nim }} @endif
                                    @if($peserta->instansi) • {{ $peserta->instansi }} @endif
                                </div>
                            @endif

                            <div class="flex items-center space-x-4 mt-3">
                                <div class="text-center">
                                    <div class="text-lg font-bold text-blue-600">{{ $peserta->kegiatans->count() }}</div>
                                    <div class="text-xs text-gray-500">Total</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-bold text-green-600">
                                        {{ $peserta->kegiatans->where('tanggal', '>=', \Carbon\Carbon::now()->startOfWeek())->count() }}
                                    </div>
                                    <div class="text-xs text-gray-500">Minggu Ini</div>
                                </div>
                            </div>

                            @if($peserta->kegiatans->isNotEmpty())
                                @php $lastKegiatan = $peserta->kegiatans->first(); @endphp
                                <div class="mt-3 p-2 bg-gray-50 rounded">
                                    <div class="text-sm font-medium text-gray-900">{{ $lastKegiatan->judul }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($lastKegiatan->tanggal)->format('d M Y') }}
                                        {{ $lastKegiatan->formatted_jam_mulai }}
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                        {{ $lastKegiatan->formatted_kategori_aktivitas }}
                                    </span>
                                </div>
                            @endif

                            <div class="flex items-center justify-end space-x-3 mt-4">
                                <a href="{{ route('pembimbing.laporan-kegiatan.show', $peserta->id) }}" 
                                   class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                    <i class="fas fa-eye mr-1"></i>Detail
                                </a>
                                <button onclick="exportPesertaData({{ $peserta->id }})" type="button"
                                        class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                    <i class="fas fa-download mr-1"></i>Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Peserta</h3>
                    <p class="text-gray-600">Belum ada peserta yang dibimbing atau sesuai filter.</p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($pesertaList->hasPages())
            <div class="mt-6">
                {{ $pesertaList->links() }}
            </div>
            @endif
        </div>
    </div>

    <script>
    // Export Excel function
    function exportExcel() {
        console.log('exportExcel function called');
        try {
            const params = new URLSearchParams();

            // Get current filter values
            const search = document.querySelector('input[name="search"]')?.value || '';
            const tanggalMulai = document.querySelector('input[name="tanggal_mulai"]')?.value || '';
            const tanggalSelesai = document.querySelector('input[name="tanggal_selesai"]')?.value || '';
            const kategori = document.querySelector('select[name="kategori"]')?.value || '';

            if (search) params.append('search', search);
            if (tanggalMulai) params.append('tanggal_mulai', tanggalMulai);
            if (tanggalSelesai) params.append('tanggal_selesai', tanggalSelesai);
            if (kategori) params.append('kategori', kategori);

            const url = `{{ route('pembimbing.laporan-kegiatan.export.excel') }}?${params.toString()}`;
            console.log('Export Excel URL:', url);
            
            // Use window.location.href for direct download
            window.location.href = url;
            
        } catch (error) {
            console.error('Error in exportExcel:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat mengunduh file Excel',
                    icon: 'error'
                });
            } else {
                alert('Error: ' + error.message);
            }
        }
    }

    // Export PDF function
    function exportPdf() {
        console.log('exportPdf function called');
        try {
            const params = new URLSearchParams();

            // Get current filter values
            const search = document.querySelector('input[name="search"]')?.value || '';
            const tanggalMulai = document.querySelector('input[name="tanggal_mulai"]')?.value || '';
            const tanggalSelesai = document.querySelector('input[name="tanggal_selesai"]')?.value || '';
            const kategori = document.querySelector('select[name="kategori"]')?.value || '';

            if (search) params.append('search', search);
            if (tanggalMulai) params.append('tanggal_mulai', tanggalMulai);
            if (tanggalSelesai) params.append('tanggal_selesai', tanggalSelesai);
            if (kategori) params.append('kategori', kategori);

            const url = `{{ route('pembimbing.laporan-kegiatan.export.pdf') }}?${params.toString()}`;
            console.log('Export PDF URL:', url);
            
            // Use window.location.href for direct download
            window.location.href = url;
            
        } catch (error) {
            console.error('Error in exportPdf:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat mengunduh file PDF',
                    icon: 'error'
                });
            } else {
                alert('Error: ' + error.message);
            }
        }
    }

    // Export per peserta function
    function exportPesertaData(pesertaId) {
        console.log('exportPesertaData function called for peserta ID:', pesertaId);
        try {
            const params = new URLSearchParams();
            params.append('peserta_id', pesertaId);

            // Get current filter values
            const tanggalMulai = document.querySelector('input[name="tanggal_mulai"]')?.value || '';
            const tanggalSelesai = document.querySelector('input[name="tanggal_selesai"]')?.value || '';
            const kategori = document.querySelector('select[name="kategori"]')?.value || '';

            if (tanggalMulai) params.append('tanggal_mulai', tanggalMulai);
            if (tanggalSelesai) params.append('tanggal_selesai', tanggalSelesai);  
            if (kategori) params.append('kategori', kategori);

            const url = `{{ route('pembimbing.laporan-kegiatan.export.pdf') }}?${params.toString()}`;
            console.log('Export Peserta PDF URL:', url);
            
            // Use window.location.href for direct download
            window.location.href = url;
            
        } catch (error) {
            console.error('Error in exportPesertaData:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat mengunduh file PDF peserta',
                    icon: 'error'
                });
            } else {
                alert('Error: ' + error.message);
            }
        }
    }

    function downloadExcel(data, filename) {
        // Simple CSV download for now
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
    </script>
@endsection