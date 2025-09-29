@extends('layouts.main')

@section('title', 'Jam Kerja - Presensi Magang')

@section('content')
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <!-- Header -->
    <div class="text-black px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-semibold flex items-center">
                <i class="fas fa-clock mr-2 text-blue-600"></i>
                Pengaturan Jam Kerja
            </h1>
            <a href="{{ route('admin.jam-kerja.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>
                Tambah Jam Kerja
            </a>
        </div>
        <p class="text-gray-600 text-sm mt-1">Kelola jadwal kerja untuk sistem presensi</p>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mx-6 mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mx-6 mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Jam Kerja List -->
    <div class="p-6">
        @if($jamKerja->count() > 0)
            <!-- Desktop Table -->
            <div class="hidden md:block">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">
                                    Nama Shift
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    Jam Kerja
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    Hari Kerja
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    Total Durasi
                                </th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($jamKerja as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $item->nama_shift }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $item->getJamKerjaDisplayName() }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-900">
                                    <div class="max-w-xs">
                                        {{ $item->getHariKerjaDisplayName() }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                    <span class="font-medium">{{ number_format($item->getTotalJamKerja(), 1) }} jam</span>
                                </td>
                                <td class="whitespace-nowrap py-4 text-center text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="{{ route('admin.jam-kerja.edit', $item) }}" 
                                           class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.jam-kerja.destroy', $item) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900" 
                                                    title="Hapus"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus jam kerja ini?')">
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
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden space-y-4">
                @foreach($jamKerja as $item)
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $item->nama_shift }}</h3>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Jam Kerja:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $item->getJamKerjaDisplayName() }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Hari Kerja:</span>
                            <span class="text-sm text-gray-900">{{ $item->getHariKerjaDisplayName() }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Total Jam:</span>
                            <span class="text-sm font-medium text-gray-900">{{ number_format($item->getTotalJamKerja(), 1) }} jam</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex space-x-3">
                            <a href="{{ route('admin.jam-kerja.edit', $item) }}" 
                               class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-edit mr-2"></i>
                                Edit
                            </a>
                            <form action="{{ route('admin.jam-kerja.destroy', $item) }}" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full inline-flex justify-center items-center px-3 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus jam kerja ini?')">
                                    <i class="fas fa-trash mr-2"></i>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="max-w-md mx-auto">
                    <i class="fas fa-clock text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada jam kerja</h3>
                    <p class="text-gray-500 mb-6">Mulai dengan menambahkan jadwal jam kerja pertama.</p>
                    <a href="{{ route('admin.jam-kerja.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Jam Kerja
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleDetail(jamKerjaId) {
    const detailRow = document.getElementById(`detail-${jamKerjaId}`);
    const expandIcon = document.getElementById(`expand-icon-${jamKerjaId}`);
    
    if (detailRow.classList.contains('hidden')) {
        // Show detail
        detailRow.classList.remove('hidden');
        expandIcon.innerHTML = '<i class="fas fa-chevron-down text-sm"></i>';
        expandIcon.classList.add('transform', 'rotate-90');
    } else {
        // Hide detail
        detailRow.classList.add('hidden');
        expandIcon.innerHTML = '<i class="fas fa-chevron-right text-sm"></i>';
        expandIcon.classList.remove('transform', 'rotate-90');
    }
}

// Prevent detail toggle when clicking on action buttons
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[onclick*="event.stopPropagation"]').forEach(element => {
        element.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
});
</script>
@endpush
