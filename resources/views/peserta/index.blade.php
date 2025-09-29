@extends('layouts.main')

@section('title', 'Data Peserta')

@section('content')
    <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Data Peserta Magang</h1>
                    <p class="text-gray-600 mt-1">Kelola data peserta magang di sistem</p>
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
                    
                    <!-- Add Button -->
                    <a href="{{ route('admin.peserta.create') }}"
                        class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Peserta
                    </a>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <h2 class="text-lg font-medium text-gray-900">Daftar Peserta</h2>
                
                <!-- Search and Filter -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="search" 
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                               placeholder="Cari peserta...">
                    </div>
                    <select id="status_filter" class="border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="non-aktif">Non-Aktif</option>
                    </select>
                    <select id="pembimbing_filter" class="border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Pembimbing</option>
                        @foreach($pembimbing as $pembimbingItem)
                            <option value="{{ $pembimbingItem->user_id }}">{{ $pembimbingItem->nama_lengkap }}</option>
                        @endforeach
                    </select>
                    <select id="lokasi_filter" class="border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Lokasi</option>
                        @foreach($lokasi as $lokasiItem)
                            <option value="{{ $lokasiItem->id }}">{{ $lokasiItem->nama_lokasi }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

            @if($peserta->count() > 0)
                <!-- Desktop Table -->
                <div class="hidden md:block">
                    <div class="overflow-x-auto ">
                        <table class="min-w-full divide-y divide-gray-200" style="min-width: 1200px;">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">
                                    Peserta
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-56">
                                    Nomor Induk dan Institusi
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-44">
                                    Pembimbing
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">
                                    Lokasi Magang
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                    Periode
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
                                    Status
                                </th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($peserta as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-4">
                                                <div class="flex items-center">
                                                    <div
                                                        class="h-10 w-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full text-white flex items-center justify-center font-semibold text-sm">
                                                        {{ strtoupper(substr($item->user->name, 0, 2)) }}
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ Str::limit($item->nama_lengkap ?? $item->user->name, 20) }}
                                                        </div>
                                                        <div class="text-xs text-gray-500">
                                                            {{ Str::limit($item->user->email, 25) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 py-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $item->nim }}</div>
                                                <div class="text-xs text-gray-500">{{ Str::limit($item->universitas, 25) }}</div>
                                                <div class="text-xs text-gray-500">{{ Str::limit($item->jurusan, 25) }}</div>
                                            </td>
                                            <td class="px-3 py-4">
                                                <div class="text-sm text-gray-900">
                                                    {{ Str::limit($item->pembimbingDetail ? $item->pembimbingDetail->nama_lengkap : 'Belum ada pembimbing', 25) }}
                                                </div>
                                            </td>
                                            <td class="px-3 py-4">
                                                <div class="text-sm text-gray-900">
                                                    {{ Str::limit($item->lokasi ? $item->lokasi->nama_lokasi : 'Belum ditentukan', 20) }}
                                                </div>
                                            </td>
                                            <td class="px-3 py-4">
                                                <div class="text-xs text-gray-900">
                                                    {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}
                                                </div>
                                            </td>
                                            <td class="px-3 py-4">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                            {{ $item->status === 'aktif'
                                ? 'bg-green-100 text-green-800'
                                : 'bg-red-100 text-red-800' }}">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-4 text-right text-sm font-medium">
                                                <div class="flex items-center justify-end space-x-1">
                                                    <a href="{{ route('admin.peserta.show', $item->id) }}"
                                                        class="text-blue-600 hover:text-blue-900 p-1.5 rounded hover:bg-blue-50" title="Lihat Detail">
                                                        <i class="fas fa-eye text-xs"></i>
                                                    </a>
                                                    <a href="{{ route('admin.peserta.edit', $item->id) }}"
                                                        class="text-yellow-600 hover:text-yellow-900 p-1.5 rounded hover:bg-yellow-50" title="Edit">
                                                        <i class="fas fa-edit text-xs"></i>
                                                    </a>
                                                    <form action="{{ route('admin.peserta.destroy', $item->id) }}" method="POST" class="inline"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data peserta ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 p-1.5 rounded hover:bg-red-50" title="Hapus">
                                                            <i class="fas fa-trash text-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden">
                    @foreach($peserta as $item)
                        <div class="border-b border-gray-200 p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="h-12 w-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full text-white flex items-center justify-center font-semibold text-lg">
                                        {{ strtoupper(substr($item->user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ $item->nama_lengkap ?? $item->user->name  }}</h3>
                                        <p class="text-sm text-gray-500">{{ $item->nim }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $item->status === 'aktif'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </div>

                            <div class="mt-3 space-y-1">
                                <p class="text-sm"><span class="font-medium">Universitas:</span> {{ $item->universitas }}</p>
                                <p class="text-sm"><span class="font-medium">Jurusan:</span> {{ $item->jurusan }}</p>
                                <p class="text-sm"><span class="font-medium">Pembimbing:</span>
                                    {{ $item->pembimbingDetail ? $item->pembimbingDetail->nama_lengkap : ($item->pembimbing ? $item->pembimbing->name : 'Belum ada pembimbing') }}</p>
                                <p class="text-sm"><span class="font-medium">Lokasi:</span>
                                    {{ $item->lokasi ? $item->lokasi->nama_lokasi : 'Belum ditentukan' }}
                                </p>
                                @if($item->lokasi)
                                    <p class="text-xs text-gray-500 ml-2">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        {{ $item->lokasi->alamat }}
                                    </p>
                                @endif
                                <p class="text-sm"><span class="font-medium">Periode:</span>
                                    {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') }} -
                                    {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d M Y') }}
                                </p>
                            </div>

                            <div class="flex items-center justify-end space-x-4 mt-4">
                                <a href="{{ route('admin.peserta.show', $item->id) }}" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye mr-1"></i>
                                    Detail
                                </a>
                                <a href="{{ route('admin.peserta.edit', $item->id) }}" class="text-yellow-600 hover:text-yellow-900">
                                    <i class="fas fa-edit mr-1"></i>
                                    Edit
                                </a>
                                <form action="{{ route('admin.peserta.destroy', $item->id) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data peserta ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash mr-1"></i>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="w-24 h-24 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-users text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Data Peserta</h3>
                    <p class="text-gray-500 mb-6">Mulai dengan menambahkan peserta magang pertama Anda.</p>
                    <a href="{{ route('admin.peserta.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Peserta
                    </a>
                </div>
            @endif
        </div>
    </div>
@push('scripts')
<script>
function exportExcel() {
    console.log('Export Excel clicked'); // Debug log
    const params = new URLSearchParams();
    
    // Ambil parameter filter yang ada
    const search = document.getElementById('search')?.value || '';
    const status = document.getElementById('status_filter')?.value || '';
    const pembimbingId = document.getElementById('pembimbing_filter')?.value || '';
    const lokasiId = document.getElementById('lokasi_filter')?.value || '';
    
    console.log('Filters:', { search, status, pembimbingId, lokasiId }); // Debug log
    
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (pembimbingId) params.append('pembimbing_id', pembimbingId);
    if (lokasiId) params.append('lokasi_id', lokasiId);
    
    // Create download link for Excel file
    const url = '{{ route("admin.peserta.export-excel") }}' + (params.toString() ? '?' + params.toString() : '');
    console.log('Export URL:', url); // Debug log
    
    // Create temporary link to trigger download
    const link = document.createElement('a');
    link.href = url;
    link.download = 'data_peserta.xlsx';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function exportPdf() {
    console.log('Export PDF clicked'); // Debug log
    const params = new URLSearchParams();
    
    // Ambil parameter filter yang ada
    const search = document.getElementById('search')?.value || '';
    const status = document.getElementById('status_filter')?.value || '';
    const pembimbingId = document.getElementById('pembimbing_filter')?.value || '';
    const lokasiId = document.getElementById('lokasi_filter')?.value || '';
    
    console.log('Filters:', { search, status, pembimbingId, lokasiId }); // Debug log
    
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (pembimbingId) params.append('pembimbing_id', pembimbingId);
    if (lokasiId) params.append('lokasi_id', lokasiId);
    
    // Redirect ke route export PDF dengan parameter
    const url = '{{ route("admin.peserta.export-pdf") }}' + (params.toString() ? '?' + params.toString() : '');
    console.log('Export URL:', url); // Debug log
    window.location.href = url;
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const statusFilter = document.getElementById('status_filter');
    const pembimbingFilter = document.getElementById('pembimbing_filter');
    const lokasiFilter = document.getElementById('lokasi_filter');
    const tableRows = document.querySelectorAll('tbody tr');
    const mobileCards = document.querySelectorAll('.md\\:hidden > div');

    function filterData() {
        // Redirect to same page with filter parameters
        const searchTerm = searchInput.value.trim();
        const statusTerm = statusFilter.value;
        const pembimbingTerm = pembimbingFilter.value;
        const lokasiTerm = lokasiFilter.value;
        
        // Build URL with parameters
        const url = new URL(window.location.href);
        url.search = ''; // Clear existing parameters
        
        if (searchTerm) url.searchParams.set('search', searchTerm);
        if (statusTerm) url.searchParams.set('status', statusTerm);
        if (pembimbingTerm) url.searchParams.set('pembimbing_id', pembimbingTerm);
        if (lokasiTerm) url.searchParams.set('lokasi_id', lokasiTerm);
        
        // Redirect to filtered URL
        window.location.href = url.toString();
    }

    // Set current filter values from URL parameters
    function setCurrentFilters() {
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.has('search')) {
            searchInput.value = urlParams.get('search');
        }
        if (urlParams.has('status')) {
            statusFilter.value = urlParams.get('status');
        }
        if (urlParams.has('pembimbing_id')) {
            pembimbingFilter.value = urlParams.get('pembimbing_id');
        }
        if (urlParams.has('lokasi_id')) {
            lokasiFilter.value = urlParams.get('lokasi_id');
        }
    }

    // Initialize filters with current URL parameters
    setCurrentFilters();

    // Add event listeners with debounce for search input
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(filterData, 500); // 500ms debounce
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterData);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterData);
    }
    
    if (pembimbingFilter) {
        pembimbingFilter.addEventListener('change', filterData);
    }
    
    if (lokasiFilter) {
        lokasiFilter.addEventListener('change', filterData);
    }
});
</script>
@endpush
@endsection
