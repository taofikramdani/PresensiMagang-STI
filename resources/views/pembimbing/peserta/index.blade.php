@extends('layouts.pembimbing')

@section('title', 'Daftar Peserta')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 mb-2">Daftar Peserta Magang</h1>
                <p class="text-sm text-gray-600">Kelola peserta yang menjadi bimbingan Anda</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500">Total: {{ $peserta->total() ?? 0 }} peserta</span>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ $totalPeserta ?? 0 }}</h3>
                    <p class="text-sm text-gray-600">Total Peserta</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-check text-green-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ $pesertaAktif ?? 0 }}</h3>
                    <p class="text-sm text-gray-600">Peserta Aktif</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-check text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ $hadirHariIni ?? 0 }}</h3>
                    <p class="text-sm text-gray-600">Hadir Hari Ini</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-times text-red-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ $tidakHadir ?? 0 }}</h3>
                    <p class="text-sm text-gray-600">Tidak Hadir</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Peserta -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Peserta</h2>
            <div class="flex items-center space-x-2">
                <!-- Export Buttons -->
                <button onclick="exportExcel()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </button>
                <button onclick="exportPdf()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </button>
            </div>
        </div>
        
        @if(isset($peserta) && $peserta->count() > 0)
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peserta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Status Hari Ini</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($peserta as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full text-white flex items-center justify-center font-semibold">
                                            {{ strtoupper(substr($p->nama_lengkap, 0, 2)) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $p->nama_lengkap }}</div>
                                            <div class="text-sm text-gray-500">NIM/NISN: {{ $p->nim }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $p->user->email ?? '-' }}</div>
                                    <div class="text-sm text-gray-500">{{ $p->no_telepon ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $p->tanggal_mulai ? $p->tanggal_mulai->format('d M Y') : '-' }}</div>
                                    <div class="text-sm text-gray-500">s/d {{ $p->tanggal_selesai ? $p->tanggal_selesai->format('d M Y') : '-' }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    @if(isset($p->status_hari_ini))
                                        @if($p->status_hari_ini == 'hadir')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check mr-1"></i>Hadir
                                            </span>
                                        @elseif($p->status_hari_ini == 'terlambat')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <i class="fas fa-clock mr-1"></i>Terlambat
                                            </span>
                                        @elseif($p->status_hari_ini == 'izin')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-calendar-times mr-1"></i>Izin
                                            </span>
                                        @elseif($p->status_hari_ini == 'sakit')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-thermometer mr-1"></i>Sakit
                                            </span>
                                        @elseif($p->status_hari_ini == 'tidak_hadir')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-times mr-1"></i>Tidak Hadir
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-question mr-1"></i>{{ ucfirst($p->status_hari_ini) }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-minus mr-1"></i>Belum Presensi
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="viewDetail({{ $p->id }})" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="{{ route('pembimbing.kehadiran.index', ['peserta' => $p->id]) }}" class="text-green-600 hover:text-green-900" title="Lihat Presensi">
                                            <i class="fas fa-calendar-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden space-y-4">
                @foreach($peserta as $p)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full text-white flex items-center justify-center font-semibold">
                                {{ strtoupper(substr($p->nama_lengkap, 0, 2)) }}
                            </div>
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900">{{ $p->nama_lengkap }}</h3>
                                <p class="text-sm text-gray-500">NIM: {{ $p->nim }}</p>
                            </div>
                            @if(isset($p->status_hari_ini))
                                @if($p->status_hari_ini == 'hadir')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Hadir
                                    </span>
                                @elseif($p->status_hari_ini == 'terlambat')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        <i class="fas fa-clock mr-1"></i>Terlambat
                                    </span>
                                @elseif($p->status_hari_ini == 'izin')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-calendar-times mr-1"></i>Izin
                                    </span>
                                @elseif($p->status_hari_ini == 'sakit')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-thermometer mr-1"></i>Sakit
                                    </span>
                                @elseif($p->status_hari_ini == 'tidak_hadir')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times mr-1"></i>Tidak Hadir
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-question mr-1"></i>{{ ucfirst($p->status_hari_ini) }}
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-minus mr-1"></i>Belum Presensi
                                </span>
                            @endif
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                            <div>
                                <span class="text-gray-500">Email:</span>
                                <p class="text-gray-900">{{ $p->user->email ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">No. HP:</span>
                                <p class="text-gray-900">{{ $p->no_telepon ?? '-' }}</p>
                            </div>
                        </div>
                        
                        <div class="text-sm mb-3">
                            <span class="text-gray-500">Periode Magang:</span>
                            <p class="text-gray-900">
                                {{ $p->tanggal_mulai ? $p->tanggal_mulai->format('d M Y') : '-' }} 
                                s/d 
                                {{ $p->tanggal_selesai ? $p->tanggal_selesai->format('d M Y') : '-' }}
                            </p>
                        </div>
                        
                        <div class="flex items-center space-x-3 pt-3 border-t border-gray-200">
                            <button onclick="viewDetail({{ $p->id }})" class="flex-1 bg-blue-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-blue-700">
                                <i class="fas fa-eye mr-1"></i>Detail
                            </button>
                            <a href="{{ route('pembimbing.kehadiran.index', ['peserta' => $p->id]) }}" class="flex-1 bg-green-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-green-700 text-center">
                                <i class="fas fa-calendar-alt mr-1"></i>Presensi
                            </a>
                            <button onclick="sendMessage({{ $p->id }})" class="flex-1 bg-purple-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-purple-700">
                                <i class="fas fa-comment mr-1"></i>Pesan
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if(isset($peserta) && $peserta->hasPages())
                <div class="mt-6">
                    {{ $peserta->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-8">
                <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Peserta</h3>
                <p class="text-gray-600">Anda belum memiliki peserta yang dibimbing</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal Detail Peserta -->
<div id="detailModal" class="fixed inset-0 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl border-2 border-gray-200 max-w-2xl w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Detail Peserta</h3>
                <button onclick="closeDetailModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="detailContent" class="space-y-4">
                <!-- Content will be loaded here -->
            </div>
            
            <div class="mt-6 flex gap-3">
                <button onclick="closeDetailModal()" class="flex-1 bg-gray-600 text-white py-2 md:py-3 rounded-xl hover:bg-gray-700 text-sm md:text-base">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Format date function
    function formatDate(dateString) {
        if (!dateString) return '-';
        
        const date = new Date(dateString);
        const months = [
            'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'
        ];
        
        const day = date.getDate();
        const month = months[date.getMonth()];
        const year = date.getFullYear();
        
        return `${day} ${month} ${year}`;
    }

    // View detail peserta
    function viewDetail(pesertaId) {
        // Show loading state
        document.getElementById('detailContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i><p class="text-gray-600 mt-2">Memuat data...</p></div>';
        document.getElementById('detailModal').classList.remove('hidden');
        document.querySelector('.space-y-6').style.filter = 'blur(2px)';
        
        // Make AJAX call to get peserta data
        fetch(`/pembimbing/peserta/${pesertaId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.peserta) {
                const peserta = data.peserta;
                let content = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                            <p class="text-gray-900">${peserta.nama_lengkap}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">NIM</label>
                            <p class="text-gray-900">${peserta.nim}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <p class="text-gray-900">${peserta.user ? peserta.user.email : '-'}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">No. Telepon</label>
                            <p class="text-gray-900">${peserta.no_telepon || '-'}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Universitas</label>
                            <p class="text-gray-900">${peserta.universitas}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jurusan</label>
                            <p class="text-gray-900">${peserta.jurusan}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <p class="text-gray-900">${peserta.status.charAt(0).toUpperCase() + peserta.status.slice(1)}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Periode Magang</label>
                            <p class="text-gray-900">${formatDate(peserta.tanggal_mulai)} s/d ${formatDate(peserta.tanggal_selesai)}</p>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Alamat</label>
                            <p class="text-gray-900">${peserta.alamat || '-'}</p>
                        </div>
                    </div>
                `;
                
                document.getElementById('detailContent').innerHTML = content;
            } else {
                document.getElementById('detailContent').innerHTML = '<div class="text-center py-8 text-red-600">Gagal memuat data peserta</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('detailContent').innerHTML = '<div class="text-center py-8 text-red-600">Terjadi kesalahan saat memuat data</div>';
        });
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
        
        // Remove blur effect from main content
        document.querySelector('.space-y-6').style.filter = 'none';
    }

    // Send message to peserta
    function sendMessage(pesertaId) {
        // You can implement WhatsApp integration or internal messaging system
        alert('Fitur kirim pesan akan segera tersedia');
    }

    // Export data to Excel
    function exportExcel() {
        window.open('{{ route("pembimbing.peserta.export.excel") }}', '_blank');
    }

    // Export data to PDF
    function exportPdf() {
        window.open('{{ route("pembimbing.peserta.export.pdf") }}', '_blank');
    }

    // Auto submit form on filter change
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.querySelector('form[method="GET"]');
        if (filterForm) {
            const selects = filterForm.querySelectorAll('select');
            
            selects.forEach(select => {
                select.addEventListener('change', function() {
                    filterForm.submit();
                });
            });
        }
    });
</script>
@endpush