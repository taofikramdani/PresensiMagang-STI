@extends('layouts.main')

@section('title', 'Data Pembimbing - Presensi Magang')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Data Pembimbing</h1>
                <p class="text-gray-600 mt-1">Kelola data pembimbing magang di sistem</p>
            </div>
            <a href="{{ route('admin.pembimbing.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>
                Tambah Pembimbing
            </a>
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
                <h2 class="text-lg font-medium text-gray-900">Daftar Pembimbing</h2>
                
                <!-- Search and Filter -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="searchInput" 
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                               placeholder="Cari pembimbing...">
                    </div>
                    <select id="statusFilter" class="border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="non_aktif">Non Aktif</option>
                    </select>
                </div>
            </div>
        </div>

        @if($pembimbing->count() > 0)
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pembimbing
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                NIP
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jabatan & Unit
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kontak
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="pembimbingTable">
                        @foreach($pembimbing as $item)
                            <tr class="hover:bg-gray-50 pembimbing-row" data-status="{{ $item->status }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-12 w-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full text-white flex items-center justify-center font-semibold text-lg">
                                            {{ strtoupper(substr($item->user->name, 0, 2)) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 searchable-name">
                                                {{ $item->nama_lengkap ?? $item->user->name  }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $item->user->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->nip }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->jabatan }}</div>
                                    <div class="text-sm text-gray-500">{{ $item->departemen }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $item->no_telepon }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $item->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <i class="fas {{ $item->status === 'aktif' ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('admin.pembimbing.show', $item) }}" 
                                           class="text-blue-600 hover:text-blue-900 transition-colors duration-200" 
                                           title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.pembimbing.edit', $item) }}" 
                                           class="text-yellow-600 hover:text-yellow-900 transition-colors duration-200" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.pembimbing.toggle-status', $item) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="text-orange-600 hover:text-orange-900 transition-colors duration-200" 
                                                    title="{{ $item->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                    onclick="return confirm('Yakin ingin mengubah status pembimbing ini?')">
                                                <i class="fas {{ $item->status === 'aktif' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.pembimbing.destroy', $item) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900 transition-colors duration-200" 
                                                    title="Hapus"
                                                    onclick="return confirm('Yakin ingin menghapus data pembimbing ini? Tindakan ini tidak dapat dibatalkan.')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden">
                <div class="space-y-4 p-4" id="pembimbingCards">
                    @foreach($pembimbing as $item)
                        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm pembimbing-card" data-status="{{ $item->status }}">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="h-12 w-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full text-white flex items-center justify-center font-semibold text-lg">
                                        {{ strtoupper(substr($item->user->name, 0, 2)) }}
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="font-medium text-gray-900 searchable-name">{{ $item->nama_lengkap ?? $item->user->name  }}</h3>
                                        <p class="text-sm text-gray-500">{{ $item->nip }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $item->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </div>
                            
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-briefcase w-4 mr-2"></i>
                                    <span>{{ $item->jabatan }} - {{ $item->unit_kerja }}</span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-envelope w-4 mr-2"></i>
                                    <span>{{ $item->user->email }}</span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-phone w-4 mr-2"></i>
                                    <span>{{ $item->no_telepon }}</span>
                                </div>
                            </div>
                            
                            <div class="flex justify-end space-x-2 mt-4 pt-3 border-t border-gray-200">
                                <a href="{{ route('admin.pembimbing.show', $item) }}" 
                                   class="text-blue-600 hover:text-blue-900 p-2" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.pembimbing.edit', $item) }}" 
                                   class="text-yellow-600 hover:text-yellow-900 p-2" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.pembimbing.toggle-status', $item) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="text-orange-600 hover:text-orange-900 p-2" 
                                            title="{{ $item->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}"
                                            onclick="return confirm('Yakin ingin mengubah status pembimbing ini?')">
                                        <i class="fas {{ $item->status === 'aktif' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.pembimbing.destroy', $item) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900 p-2" 
                                            title="Hapus"
                                            onclick="return confirm('Yakin ingin menghapus data pembimbing ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-user-tie text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Data Pembimbing</h3>
                <p class="text-gray-500 mb-6">Mulai dengan menambahkan data pembimbing baru</p>
                <a href="{{ route('admin.pembimbing.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Pembimbing
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const tableRows = document.querySelectorAll('.pembimbing-row');
    const mobileCards = document.querySelectorAll('.pembimbing-card');

    function filterData() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusTerm = statusFilter.value;

        // Filter desktop table
        tableRows.forEach(row => {
            const name = row.querySelector('.searchable-name').textContent.toLowerCase();
            const status = row.dataset.status;
            
            const matchesSearch = name.includes(searchTerm);
            const matchesStatus = !statusTerm || status === statusTerm;
            
            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Filter mobile cards
        mobileCards.forEach(card => {
            const name = card.querySelector('.searchable-name').textContent.toLowerCase();
            const status = card.dataset.status;
            
            const matchesSearch = name.includes(searchTerm);
            const matchesStatus = !statusTerm || status === statusTerm;
            
            if (matchesSearch && matchesStatus) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterData);
    statusFilter.addEventListener('change', filterData);
});
</script>
@endpush
@endsection
