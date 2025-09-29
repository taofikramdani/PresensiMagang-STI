@extends('layouts.pembimbing')

@section('title', 'Approval Izin & Sakit')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 mb-2">Approval Izin & Sakit</h1>
                <p class="text-sm text-gray-600">Kelola permohonan izin dan sakit dari peserta bimbingan Anda</p>
            </div>
            <div class="flex items-center space-x-2">
                @if(($izinPending ?? 0) > 0)
                    <span class="bg-red-500 text-white text-sm rounded-full h-8 w-8 flex items-center justify-center font-semibold">
                        {{ $izinPending }}
                    </span>
                @endif
                <span class="text-sm text-gray-500">{{ $izinPending ?? 0 }} menunggu persetujuan</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Peserta</label>
                <select name="peserta" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Peserta</option>
                    @if(isset($pesertaList))
                        @foreach($pesertaList as $p)
                            <option value="{{ $p->id }}" {{ request('peserta') == $p->id ? 'selected' : '' }}>
                                {{ $p->nama_lengkap }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis</label>
                <select name="jenis" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Jenis</option>
                    <option value="izin" {{ request('jenis') == 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ request('jenis') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                <input type="month" name="bulan" value="{{ request('bulan') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 md:px-6 md:py-2 rounded-lg hover:bg-blue-700 text-sm md:text-base">
                    <i class="fas fa-search mr-1 md:mr-2"></i><span class="hidden sm:inline">Filter</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ $totalPending ?? 0 }}</h3>
                    <p class="text-sm text-gray-600">Menunggu Persetujuan</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check text-green-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ $totalDisetujui ?? 0 }}</h3>
                    <p class="text-sm text-gray-600">Disetujui</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times text-red-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ $totalDitolak ?? 0 }}</h3>
                    <p class="text-sm text-gray-600">Ditolak</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ $totalPerizinan ?? 0 }}</h3>
                    <p class="text-sm text-gray-600">Total Perizinan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Perizinan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Perizinan</h2>
        </div>
        
        @if(isset($perizinanList) && $perizinanList->count() > 0)
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peserta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis & Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pengajuan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($perizinanList as $izin)
                            <tr class="hover:bg-gray-50 @if($izin->status == 'disetujui') bg-green-50 @elseif($izin->status == 'ditolak') bg-red-50 @endif">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full text-white flex items-center justify-center font-semibold">
                                            {{ strtoupper(substr($izin->peserta->nama_lengkap, 0, 2)) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $izin->peserta->nama_lengkap }}</div>
                                            <div class="text-sm text-gray-500">{{ $izin->peserta->nim }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $izin->jenis == 'izin' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                            {{ ucfirst($izin->jenis) }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $izin->tanggal->timezone('Asia/Jakarta')->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs">
                                        {{ Str::limit($izin->keterangan, 100) }}
                                    </div>
                                    @if($izin->bukti_dokumen)
                                        <div class="text-xs text-blue-600 mt-1">
                                            <i class="fas fa-paperclip mr-1"></i>Ada bukti dokumen
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($izin->status == 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>Menunggu
                                        </span>
                                    @elseif($izin->status == 'disetujui')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>Disetujui
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times mr-1"></i>Ditolak
                                        </span>
                                    @endif
                                    @if($izin->catatan_pembimbing)
                                        <div class="text-xs text-gray-500 mt-1">{{ Str::limit($izin->catatan_pembimbing, 50) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $izin->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="viewIzinDetail({{ $izin->id }})" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($izin->bukti_dokumen)
                                            <button onclick="viewBuktiDokumen('{{ $izin->bukti_dokumen_url }}', '{{ $izin->bukti_dokumen_type }}')" class="text-green-600 hover:text-green-900" title="Lihat Bukti">
                                                <i class="fas fa-file-alt"></i>
                                            </button>
                                        @endif
                                        @if($izin->status == 'pending')
                                            <button onclick="approveIzin({{ $izin->id }})" class="text-green-600 hover:text-green-900" title="Setujui">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button onclick="rejectIzin({{ $izin->id }})" class="text-red-600 hover:text-red-900" title="Tolak">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden space-y-4">
                @foreach($perizinanList as $izin)
                    <div class="border border-gray-200 rounded-lg p-4 @if($izin->status == 'disetujui') border-green-200 bg-green-50 @elseif($izin->status == 'ditolak') border-red-200 bg-red-50 @endif">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full text-white flex items-center justify-center font-semibold">
                                    {{ strtoupper(substr($izin->peserta->nama_lengkap, 0, 2)) }}
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ $izin->peserta->nama_lengkap }}</h3>
                                    <p class="text-sm text-gray-500">{{ $izin->tanggal->format('d M Y') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $izin->jenis == 'izin' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ ucfirst($izin->jenis) }}
                                </span>
                                @if($izin->status == 'pending')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i>Menunggu
                                    </span>
                                @elseif($izin->status == 'disetujui')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Disetujui
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times mr-1"></i>Ditolak
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-3">{{ Str::limit($izin->keterangan, 120) }}</p>
                        
                        @if($izin->bukti_dokumen)
                            <div class="flex items-center text-sm text-blue-600 mb-3">
                                <i class="fas fa-paperclip mr-2"></i>
                                <span>Ada bukti dokumen</span>
                            </div>
                        @endif
                        
                        @if($izin->catatan_pembimbing)
                            <div class="text-sm text-gray-600 mb-3 p-2 bg-gray-100 rounded">
                                <strong>Catatan:</strong> {{ $izin->catatan_pembimbing }}
                            </div>
                        @endif
                        
                        <div class="text-xs text-gray-500 mb-3">
                            Diajukan: {{ $izin->created_at->format('d M Y, H:i') }}
                        </div>
                        
                        <div class="flex items-center space-x-2 pt-3 border-t border-gray-200">
                            <button onclick="viewIzinDetail({{ $izin->id }})" class="flex-1 bg-blue-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-blue-700">
                                <i class="fas fa-eye mr-1"></i>Detail
                            </button>
                            @if($izin->bukti_dokumen)
                                <button onclick="viewBuktiDokumen('{{ $izin->bukti_dokumen_url }}', '{{ $izin->bukti_dokumen_type }}')" class="bg-green-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-green-700">
                                    <i class="fas fa-file-alt"></i>
                                </button>
                            @endif
                            @if($izin->status == 'pending')
                                <button onclick="approveIzin({{ $izin->id }})" class="bg-green-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-green-700">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="rejectIzin({{ $izin->id }})" class="bg-red-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if(isset($perizinanList) && $perizinanList->hasPages())
                <div class="mt-6">
                    {{ $perizinanList->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-8">
                <i class="fas fa-clipboard-list text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Perizinan</h3>
                <p class="text-gray-600">Belum ada pengajuan izin/sakit untuk filter yang dipilih</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal Detail Izin -->
<div id="izinDetailModal" class="fixed inset-0 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl border-2 border-gray-200 max-w-2xl w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Detail Perizinan</h3>
                <button onclick="closeIzinDetailModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="izinDetailContent" class="space-y-4">
                <!-- Content will be loaded here -->
            </div>
            
            <div class="mt-6 flex gap-3">
                <button onclick="closeIzinDetailModal()" class="flex-1 bg-gray-600 text-white py-2 md:py-3 rounded-xl hover:bg-gray-700 text-sm md:text-base">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Approval/Rejection -->
<div id="approvalModal" class="fixed inset-0 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl border-2 border-gray-200 max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 id="approvalModalTitle" class="text-lg font-semibold text-gray-900">Konfirmasi Persetujuan</h3>
                <button onclick="closeApprovalModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="approvalForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                    <textarea id="catatanPembimbing" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="closeApprovalModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" id="approvalSubmitBtn" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal untuk menampilkan bukti dokumen -->
<div id="buktiDokumenModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative mx-auto border w-full max-w-4xl max-h-[90vh] shadow-2xl rounded-lg bg-white overflow-hidden">
            <!-- Header -->
            <div class="flex justify-between items-center p-4 border-b bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-file-alt mr-2 text-blue-600"></i>
                    Bukti Dokumen
                </h3>
                <button onclick="closeBuktiDokumenModal()" class="text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded-full p-2 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <!-- Content -->
            <div id="buktiDokumenContent" class="p-4 max-h-[calc(90vh-120px)] overflow-auto">
                <!-- Content akan diisi via JavaScript -->
            </div>
            
            <!-- Footer -->
            <div class="flex justify-end p-4 border-t bg-gray-50 space-x-2">
                <button onclick="closeBuktiDokumenModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-times mr-1"></i>
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentIzinId = null;
    let currentAction = null;

    // View izin detail
    function viewIzinDetail(izinId) {
        // Get data from existing perizinan list
        const perizinanData = @json($perizinanList->items());
        const izinData = perizinanData.find(p => p.id === izinId);
        
        if (!izinData) {
            alert('Data perizinan tidak ditemukan');
            return;
        }
        
        let statusBadge = '';
        if (izinData.status === 'pending') {
            statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-clock mr-1"></i>Menunggu Persetujuan</span>';
        } else if (izinData.status === 'disetujui') {
            statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check mr-1"></i>Disetujui</span>';
        } else {
            statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"><i class="fas fa-times mr-1"></i>Ditolak</span>';
        }
        
        let content = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Peserta</label>
                    <p class="text-gray-900">${izinData.peserta.nama_lengkap}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">NIM</label>
                    <p class="text-gray-900">${izinData.peserta.nim}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jenis Perizinan</label>
                    <p class="text-gray-900">${izinData.jenis === 'izin' ? 'Izin' : 'Sakit'}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                    <p class="text-gray-900">${new Date(izinData.tanggal).toLocaleDateString('id-ID', { 
                        day: 'numeric', 
                        month: 'long', 
                        year: 'numeric' 
                    })}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <div class="mt-1">${statusBadge}</div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Bukti Dokumen</label>
                    <p class="text-gray-900">${izinData.bukti_dokumen ? 'Ada' : 'Tidak ada'}</p>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Keterangan</label>
                    <p class="text-gray-900">${izinData.keterangan}</p>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Tanggal Pengajuan</label>
                    <p class="text-gray-900">${new Date(izinData.created_at).toLocaleDateString('id-ID', { 
                        day: 'numeric', 
                        month: 'long', 
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })}</p>
                </div>
        `;
        
        if (izinData.catatan_pembimbing) {
            content += `
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Catatan Pembimbing</label>
                    <p class="text-gray-900">${izinData.catatan_pembimbing}</p>
                </div>
            `;
        }
        
        if (izinData.tanggal_approval) {
            content += `
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Tanggal ${izinData.status === 'disetujui' ? 'Persetujuan' : 'Penolakan'}</label>
                    <p class="text-gray-900">${new Date(izinData.tanggal_approval).toLocaleDateString('id-ID', { 
                        day: 'numeric', 
                        month: 'long', 
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })}</p>
                </div>
            `;
        }
        
        content += '</div>';
        
        document.getElementById('izinDetailContent').innerHTML = content;
        document.getElementById('izinDetailModal').classList.remove('hidden');
        
        // Add blur effect to main content
        document.querySelector('.space-y-6').style.filter = 'blur(2px)';
    }

    function closeIzinDetailModal() {
        document.getElementById('izinDetailModal').classList.add('hidden');
        
        // Remove blur effect from main content
        document.querySelector('.space-y-6').style.filter = 'none';
    }

    // Approve izin
    function approveIzin(izinId) {
        currentIzinId = izinId;
        currentAction = 'approve';
        document.getElementById('approvalModalTitle').textContent = 'Konfirmasi Persetujuan';
        document.getElementById('approvalSubmitBtn').textContent = 'Setujui';
        document.getElementById('approvalSubmitBtn').className = 'flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700';
        document.getElementById('catatanPembimbing').value = '';
        document.getElementById('approvalModal').classList.remove('hidden');
        
        // Add blur effect to main content
        document.querySelector('.space-y-6').style.filter = 'blur(2px)';
    }

    // Reject izin
    function rejectIzin(izinId) {
        currentIzinId = izinId;
        currentAction = 'reject';
        document.getElementById('approvalModalTitle').textContent = 'Konfirmasi Penolakan';
        document.getElementById('approvalSubmitBtn').textContent = 'Tolak';
        document.getElementById('approvalSubmitBtn').className = 'flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700';
        document.getElementById('catatanPembimbing').value = '';
        document.getElementById('approvalModal').classList.remove('hidden');
        
        // Add blur effect to main content
        document.querySelector('.space-y-6').style.filter = 'blur(2px)';
    }

    function closeApprovalModal() {
        document.getElementById('approvalModal').classList.add('hidden');
        currentIzinId = null;
        currentAction = null;
        
        // Remove blur effect from main content
        document.querySelector('.space-y-6').style.filter = 'none';
    }

    // Handle approval form submission
    document.getElementById('approvalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const catatan = document.getElementById('catatanPembimbing').value;
        const submitBtn = document.getElementById('approvalSubmitBtn');
        const originalText = submitBtn.textContent;
        
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.textContent = 'Memproses...';
        
        // Determine the URL based on action
        const url = currentAction === 'approve' 
            ? `/pembimbing/izin/${currentIzinId}/approve`
            : `/pembimbing/izin/${currentIzinId}/reject`;
        
        // Make AJAX request
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                catatan_pembimbing: catatan
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const successMessage = data.message || (currentAction === 'approve' ? 'Perizinan berhasil disetujui!' : 'Perizinan berhasil ditolak!');
                
                // Create and show success notification
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                notification.textContent = successMessage;
                document.body.appendChild(notification);
                
                // Remove notification after 3 seconds
                setTimeout(() => {
                    notification.remove();
                }, 3000);
                
                closeApprovalModal();
                
                // Refresh page to show updated data
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                alert(data.message || 'Terjadi kesalahan. Silakan coba lagi.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan. Silakan coba lagi.');
        })
        .finally(() => {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });

    // View bukti dokumen
    function viewBuktiDokumen(url, type) {
        const modal = document.getElementById('buktiDokumenModal');
        const content = document.getElementById('buktiDokumenContent');
        
        // Show loading
        content.innerHTML = `
            <div class="flex items-center justify-center py-12">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-3"></i>
                    <p class="text-gray-600">Memuat dokumen...</p>
                </div>
            </div>
        `;
        
        modal.classList.remove('hidden');
        
        // Load content based on file type
        setTimeout(() => {
            if (type === 'pdf') {
                content.innerHTML = `
                    <div class="text-center">
                        <div class="space-y-3">
                            <br>
                            <a href="${url}" download class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-download mr-2"></i>
                                Download PDF
                            </a>
                        </div>
                        <div class="mt-6 border rounded-lg overflow-hidden">
                            <embed src="${url}" type="application/pdf" width="100%" height="400px" class="border-0" />
                        </div>
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <div class="text-center">
                        <div class="mb-4">
                            <img src="${url}" alt="Bukti Dokumen" class="max-w-full h-auto mx-auto rounded-lg shadow-lg border" style="max-height: 60vh;" />
                        </div>
                        <div class="space-y-3">
                            <a href="${url}" target="_blank" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-search-plus mr-2"></i>
                                Lihat Ukuran Penuh
                            </a>
                            <br>
                            <a href="${url}" download class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-download mr-2"></i>
                                Download Gambar
                            </a>
                        </div>
                    </div>
                `;
            }
        }, 500);
        
        // Add blur effect to main content
        document.querySelector('.space-y-6').style.filter = 'blur(2px)';
        document.body.style.overflow = 'hidden';
    }

    function closeBuktiDokumenModal() {
        const modal = document.getElementById('buktiDokumenModal');
        modal.classList.add('hidden');
        
        // Remove blur effect from main content
        document.querySelector('.space-y-6').style.filter = 'none';
        document.body.style.overflow = 'auto';
    }

    // Export izin data
    function exportIzin() {
        // Implement export functionality
        alert('Export Excel akan segera tersedia');
    }

    // Auto submit form on filter change
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.querySelector('form[method="GET"]');
        if (filterForm) {
            const selects = filterForm.querySelectorAll('select, input[type="month"]');
            
            selects.forEach(select => {
                select.addEventListener('change', function() {
                    filterForm.submit();
                });
            });
        }

        // Close modal when clicking outside
        document.getElementById('buktiDokumenModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeBuktiDokumenModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('buktiDokumenModal');
                if (!modal.classList.contains('hidden')) {
                    closeBuktiDokumenModal();
                }
            }
        });
    });
</script>
@endpush