@extends('layouts.main')

@section('title', 'Data Administrator - Presensi Magang')

@section('content')
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Data Administrator</h1>
                    <p class="text-gray-600 mt-1">Kelola data administrator sistem</p>
                </div>
                @if($user->isAdmin())
                <a href="{{ route('admin.administrator.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Administrator
                </a>
                @endif
            </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Daftar Administrator</h2>
            </div>

            @if($administrators->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Administrator
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bergabung
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($administrators as $administrator)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <i class="fas fa-user-shield text-blue-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $administrator->name }}</div>
                                                <div class="text-sm text-gray-500">ID: {{ $administrator->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $administrator->email }}</div>
                                        @if($administrator->email_verified_at)
                                            <div class="text-sm text-green-600">
                                                <i class="fas fa-check-circle mr-1"></i>Terverifikasi
                                            </div>
                                        @else
                                            <div class="text-sm text-red-600">
                                                <i class="fas fa-times-circle mr-1"></i>Belum terverifikasi
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($administrator->is_active)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-circle mr-1 text-green-400"></i>
                                                Aktif
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-circle mr-1 text-red-400"></i>
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ $administrator->created_at->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-400">{{ $administrator->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <!-- View Button -->
                                            <a href="{{ route('admin.administrator.show', $administrator) }}"
                                                class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                                title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if($user->isAdmin())
                                                <!-- Edit Button -->
                                                <a href="{{ route('admin.administrator.edit', $administrator) }}"
                                                    class="text-green-600 hover:text-green-900 transition-colors duration-200"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <!-- Toggle Status Button -->
                                                @if($administrator->id !== auth()->id())
                                                    <form action="{{ route('admin.administrator.toggle-status', $administrator) }}"
                                                        method="POST" class="inline-block"
                                                        onsubmit="return confirm('Yakin ingin mengubah status administrator ini?')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                            class="text-yellow-600 hover:text-yellow-900 transition-colors duration-200"
                                                            title="{{ $administrator->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                            <i
                                                                class="fas fa-{{ $administrator->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                                        </button>
                                                    </form>

                                                    <!-- Delete Button -->
                                                    <form action="{{ route('admin.administrator.destroy', $administrator) }}" method="POST"
                                                        class="inline-block"
                                                        onsubmit="return confirm('Yakin ingin menghapus administrator ini? Tindakan ini tidak dapat dibatalkan.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                            title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($administrators->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $administrators->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <i class="fas fa-users-cog text-gray-400 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data administrator</h3>
                    <p class="text-gray-500 mb-4">Belum ada administrator yang terdaftar dalam sistem.</p>
                    @if($user->isAdmin())
                        <a href="{{ route('admin.administrator.create') }}"
                            class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                            <i class="fas fa-plus mr-1.5"></i>
                            Tambah Administrator Pertama
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection