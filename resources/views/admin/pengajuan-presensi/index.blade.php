@extends('layouts.main')

@section('title', 'Pengajuan Presensi - Admin')

@section('content')
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <!-- Page Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Pengajuan Presensi</h1>
                <p class="mt-1 text-sm text-gray-600">Kelola persetujuan pengajuan presensi dari peserta</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="p-6 border-b border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clipboard-list text-blue-600 text-2xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-600">Total Pengajuan</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $statistics['total'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-600">Menunggu Persetujuan</p>
                        <p class="text-2xl font-bold text-yellow-900">{{ $statistics['pending'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-600">Disetujui</p>
                        <p class="text-2xl font-bold text-green-900">{{ $statistics['disetujui'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-600">Ditolak</p>
                        <p class="text-2xl font-bold text-red-900">{{ $statistics['ditolak'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="p-6 border-b border-gray-200">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Dari</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" 
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Sampai</label>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" 
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Peserta</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama peserta..."
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <button type="button" onclick="clearFilters()" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 text-sm font-medium">
                    <i class="fas fa-times mr-2"></i>Clear
                </button>
            </div>
        </form>
    </div>

    <!-- Pengajuan Table -->
    <div class="p-6">
        <div class="overflow-hidden md:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Peserta</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Jenis Pengajuan</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tanggal Presensi</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Jam</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tanggal Pengajuan</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($pengajuans as $pengajuan)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm">
                                <div class="flex items-center">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $pengajuan->peserta->nama_lengkap }}</div>
                                        <div class="text-gray-500">{{ $pengajuan->peserta->pembimbingDetail->nama_lengkap ?? 'No Pembimbing' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $pengajuan->jenis_pengajuan === 'lupa_checkout' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ $pengajuan->jenis_pengajuan_display }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                {{ $pengajuan->tanggal_presensi->format('d/m/Y') }}
                                <div class="text-xs text-gray-400">{{ $pengajuan->tanggal_presensi->locale('id')->dayName }}</div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <div>
                                    @if($pengajuan->jam_masuk)
                                        <div>Masuk: {{ $pengajuan->jam_masuk }}</div>
                                    @endif
                                    @if($pengajuan->jam_keluar)
                                        <div>Keluar: {{ $pengajuan->jam_keluar }}</div>
                                    @endif
                                    @if(!$pengajuan->jam_masuk && !$pengajuan->jam_keluar)
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'disetujui' => 'bg-green-100 text-green-800',
                                        'ditolak' => 'bg-red-100 text-red-800'
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$pengajuan->status] }}">
                                    {{ ucfirst($pengajuan->status) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                {{ $pengajuan->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <div class="flex space-x-2">
                                    <button onclick="viewDetail({{ $pengajuan->id }})" 
                                            class="text-blue-600 hover:text-blue-900 font-medium">
                                        <i class="fas fa-eye mr-1"></i>Detail
                                    </button>
                                    
                                    @if($pengajuan->status === 'pending')
                                        <button onclick="approveModal({{ $pengajuan->id }})" 
                                                class="text-green-600 hover:text-green-900 font-medium">
                                            <i class="fas fa-check mr-1"></i>Setujui
                                        </button>
                                        <button onclick="rejectModal({{ $pengajuan->id }})" 
                                                class="text-red-600 hover:text-red-900 font-medium">
                                            <i class="fas fa-times mr-1"></i>Tolak
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">
                                <i class="fas fa-clipboard-list text-4xl text-gray-300 mb-2"></i>
                                <p>Belum ada pengajuan presensi</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($pengajuans->hasPages())
            <div class="mt-6">
                {{ $pengajuans->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Detail -->
<div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Detail Pengajuan Presensi</h3>
                <button onclick="closeDetailModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="detailContent" class="space-y-4">
                <!-- Content will be loaded here -->
            </div>
            
            <div class="flex justify-end mt-6">
                <button onclick="closeDetailModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Approve -->
<div id="approveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Setujui Pengajuan</h3>
                <button onclick="closeApproveModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="approveForm">
                <input type="hidden" id="approve_pengajuan_id" name="pengajuan_id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan (Opsional)</label>
                    <textarea id="approve_keterangan" name="keterangan" rows="3" 
                              placeholder="Tambahkan keterangan jika diperlukan..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm resize-none"></textarea>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="closeApproveModal()" 
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-check mr-2"></i>Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Tolak Pengajuan</h3>
                <button onclick="closeRejectModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="rejectForm">
                <input type="hidden" id="reject_pengajuan_id" name="pengajuan_id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea id="reject_keterangan" name="keterangan" rows="4" required
                              placeholder="Berikan alasan mengapa pengajuan ditolak..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm resize-none"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter</p>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="closeRejectModal()" 
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                        <i class="fas fa-times mr-2"></i>Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// View detail pengajuan
function viewDetail(id) {
    fetch(`/admin/pengajuan-presensi/${id}`)
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('detailContent');
            content.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Peserta</label>
                        <p class="mt-1 text-sm text-gray-900">${data.peserta.nama_lengkap}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pembimbing</label>
                        <p class="mt-1 text-sm text-gray-900">${data.peserta.pembimbing_detail ? data.peserta.pembimbing_detail.nama_lengkap : 'No Pembimbing'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jenis Pengajuan</label>
                        <p class="mt-1 text-sm text-gray-900">${data.jenis_pengajuan_display}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Presensi</label>
                        <p class="mt-1 text-sm text-gray-900">${new Date(data.tanggal_presensi).toLocaleDateString('id-ID')}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jam Masuk</label>
                        <p class="mt-1 text-sm text-gray-900">${data.jam_masuk || '-'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jam Keluar</label>
                        <p class="mt-1 text-sm text-gray-900">${data.jam_keluar || '-'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <p class="mt-1 text-sm text-gray-900">${data.status}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Pengajuan</label>
                        <p class="mt-1 text-sm text-gray-900">${new Date(data.created_at).toLocaleDateString('id-ID')} ${new Date(data.created_at).toLocaleTimeString('id-ID')}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Penjelasan</label>
                    <p class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">${data.penjelasan}</p>
                </div>
                ${data.keterangan_pembimbing ? `
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Keterangan Admin</label>
                    <p class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">${data.keterangan_pembimbing}</p>
                </div>
                ` : ''}
                ${data.approver ? `
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Diproses oleh</label>
                    <p class="mt-1 text-sm text-gray-900">${data.approver.name} pada ${new Date(data.approved_at).toLocaleDateString('id-ID')} ${new Date(data.approved_at).toLocaleTimeString('id-ID')}</p>
                </div>
                ` : ''}
            `;
            document.getElementById('detailModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error!', 'Gagal memuat detail pengajuan', 'error');
        });
}

// Open approve modal
function approveModal(id) {
    document.getElementById('approve_pengajuan_id').value = id;
    document.getElementById('approve_keterangan').value = '';
    document.getElementById('approveModal').classList.remove('hidden');
}

// Open reject modal
function rejectModal(id) {
    document.getElementById('reject_pengajuan_id').value = id;
    document.getElementById('reject_keterangan').value = '';
    document.getElementById('rejectModal').classList.remove('hidden');
}

// Close modals
function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

// Handle approve form
document.getElementById('approveForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const id = document.getElementById('approve_pengajuan_id').value;
    const keterangan = document.getElementById('approve_keterangan').value;
    
    fetch(`/admin/pengajuan-presensi/${id}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ keterangan })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Berhasil!', data.message, 'success').then(() => {
                closeApproveModal();
                location.reload();
            });
        } else {
            Swal.fire('Error!', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error!', 'Terjadi kesalahan saat memproses pengajuan', 'error');
    });
});

// Handle reject form
document.getElementById('rejectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const id = document.getElementById('reject_pengajuan_id').value;
    const keterangan = document.getElementById('reject_keterangan').value;
    
    if (keterangan.length < 10) {
        Swal.fire('Error!', 'Alasan penolakan minimal 10 karakter', 'error');
        return;
    }
    
    fetch(`/admin/pengajuan-presensi/${id}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ keterangan })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Berhasil!', data.message, 'success').then(() => {
                closeRejectModal();
                location.reload();
            });
        } else {
            Swal.fire('Error!', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error!', 'Terjadi kesalahan saat memproses pengajuan', 'error');
    });
});

// Clear filters
function clearFilters() {
    window.location.href = window.location.pathname;
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.id === 'detailModal') closeDetailModal();
    if (e.target.id === 'approveModal') closeApproveModal();
    if (e.target.id === 'rejectModal') closeRejectModal();
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDetailModal();
        closeApproveModal();
        closeRejectModal();
    }
});
</script>
@endsection