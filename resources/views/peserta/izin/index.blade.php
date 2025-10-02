@extends('layouts.peserta')

@section('title', 'Perizinan | Day-In')

@section('content')
<div class="space-y-6 p-4">
    <!-- Header dengan Tabs -->
    <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-200">
        <div class="flex flex-col md:flex-row justify-start items-start gap-4">
            <div>
            <h1 class="text-xl font-bold text-gray-800 mb-2">Perizinan & Pengajuan Presensi</h1>
            <p class="text-sm text-gray-600">Kelola izin, sakit, dan pengajuan presensi</p>
            </div>
        </div>
        
        <!-- Tabs Navigation -->
        <div class="mt-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button onclick="switchTab('izin')" id="tab-izin" class="tab-button active whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm border-gray-800 text-gray-800">
                    <i class="fas fa-user-times mr-2"></i>Izin & Sakit
                </button>
                <button onclick="switchTab('presensi')" id="tab-presensi" class="tab-button whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-400">
                    <i class="fas fa-clock mr-2"></i>Pengajuan Presensi
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Content: Izin & Sakit -->
    <div id="content-izin" class="tab-content">
        <!-- Button untuk menampilkan form -->
        <div class="mb-6">
            <button onclick="toggleForm()" id="toggleFormBtn" class="bg-blue-600 text-white font-bold py-3 px-6 rounded-xl hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Ajukan Izin/Sakit
            </button>
        </div>

    <!-- Form Pengajuan (Hidden by default) -->
    <div id="formPengajuan" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" style="display: none;">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Ajukan Izin/Sakit</h2>
            <button onclick="toggleForm()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
                    <form method="POST" action="{{ route('peserta.pengajuan-presensi.store') }}" enctype="multipart/form-data" class="space-y-4" id="presensiForm">
            @csrf
            
            <!-- Jenis -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Permohonan</label>
                <select name="jenis" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">Pilih jenis permohonan</option>
                    <option value="izin" {{ old('jenis') == 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ old('jenis') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                </select>
            </div>

            <!-- Tanggal -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                <input type="date" name="tanggal" value="{{ old('tanggal') }}" min="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan/Keterangan</label>
                <textarea name="keterangan" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Jelaskan alasan izin/sakit dengan detail..." required>{{ old('keterangan') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter</p>
            </div>

            <!-- Upload Bukti Dokumen -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Dokumen (Opsional)</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-blue-400 transition-colors" id="dropZone">
                    <div class="text-center" id="uploadContent">
                        <i class="fas fa-cloud-upload-alt text-gray-400 text-2xl mb-2"></i>
                        <p class="text-sm text-gray-600 mb-2">Klik untuk upload atau drag & drop</p>
                        <p class="text-xs text-gray-500">PDF, JPG, JPEG, PNG (Max 2MB)</p>
                        <p class="text-xs text-gray-500 mt-1">Untuk sakit: surat dokter. Untuk izin: surat keterangan</p>
                        <input type="file" name="bukti_dokumen" id="buktiDokumen" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                <div id="filePreview" class="mt-2 hidden">
                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                            <span id="fileName" class="text-sm text-gray-700"></span>
                            <span id="fileSize" class="text-xs text-gray-500 ml-2"></span>
                        </div>
                        <button type="button" onclick="removeFile()" class="text-red-600 hover:text-red-800 text-sm">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-2 px-4 md:py-3 md:px-6 rounded-xl hover:bg-blue-700 text-sm md:text-base">
                    <i class="fas fa-paper-plane mr-1 md:mr-2"></i>
                    Kirim Permohonan
                </button>
                <button type="button" onclick="toggleForm()" class="px-4 py-2 md:px-6 md:py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 text-sm md:text-base">
                    Batal
                </button>
            </div>
        </form>
    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-base font-bold text-gray-900">{{ $perizinans->where('status', 'pending')->count() }}</h3>
                    <p class="text-xs text-gray-600">Menunggu Persetujuan</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check text-green-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-base font-bold text-gray-900">{{ $perizinans->where('status', 'disetujui')->count() }}</h3>
                    <p class="text-xs text-gray-600">Disetujui</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times text-red-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-base font-bold text-gray-900">{{ $perizinans->where('status', 'ditolak')->count() }}</h3>
                    <p class="text-xs text-gray-600">Ditolak</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
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
                <input type="month" name="bulan" value="{{ request('bulan') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 md:px-6 md:py-2 rounded-lg hover:bg-blue-700 text-sm md:text-base">
                    <i class="fas fa-search mr-1 md:mr-2"></i><span class="hidden sm:inline">Filter</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Daftar Perizinan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Pengajuan</h2>
        
        @if($perizinans->count() > 0)
            <div class="space-y-4">
                @foreach($perizinans as $perizinan)
                    <div class="border border-gray-200 rounded-lg p-4 
                        @if($perizinan->status == 'disetujui') border-green-200 bg-green-50 
                        @elseif($perizinan->status == 'ditolak') border-red-200 bg-red-50 
                        @endif">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center
                                    @if($perizinan->status == 'pending') bg-yellow-100
                                    @elseif($perizinan->status == 'disetujui') bg-green-100
                                    @else bg-red-100
                                    @endif">
                                    @if($perizinan->status == 'pending')
                                        <i class="fas fa-clock text-yellow-600"></i>
                                    @elseif($perizinan->status == 'disetujui')
                                        <i class="fas fa-check text-green-600"></i>
                                    @else
                                        <i class="fas fa-times text-red-600"></i>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $perizinan->jenis_label }}</p>
                                    <p class="text-sm text-gray-600">{{ $perizinan->tanggal->format('d F Y') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 text-xs rounded-full font-medium {{ $perizinan->status_color }}
                                    @if($perizinan->status == 'pending') bg-yellow-100
                                    @elseif($perizinan->status == 'disetujui') bg-green-100
                                    @else bg-red-100
                                    @endif">
                                    {{ $perizinan->status_label }}
                                </span>
                                
                                @if($perizinan->status == 'pending')
                                    <button onclick="editPerizinan({{ $perizinan->id }}, '{{ $perizinan->jenis }}', '{{ $perizinan->tanggal->format('Y-m-d') }}', '{{ addslashes($perizinan->keterangan) }}')" class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('peserta.izin.destroy', $perizinan) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus perizinan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                <button onclick="showDetail({{ $perizinan->id }})" class="text-gray-600 hover:text-gray-800 text-sm">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-2">{{ Str::limit($perizinan->keterangan, 100) }}</p>
                        
                        @if($perizinan->bukti_dokumen)
                            <div class="flex items-center justify-between text-sm bg-blue-50 border border-blue-200 rounded-lg p-3 mb-2">
                                <div class="flex items-center text-blue-700">
                                    <i class="fas fa-paperclip mr-2"></i>
                                    <span class="font-medium">Bukti dokumen tersedia</span>
                                    @if($perizinan->bukti_dokumen_type === 'pdf')
                                        <i class="fas fa-file-pdf ml-2 text-red-500"></i>
                                    @else
                                        <i class="fas fa-image ml-2 text-green-500"></i>
                                    @endif
                                </div>
                                <button onclick="showBuktiDokumen('{{ $perizinan->bukti_dokumen_url }}', '{{ $perizinan->bukti_dokumen_type }}')" 
                                        class="flex items-center px-3 py-1 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-eye mr-1"></i>
                                    Lihat
                                </button>
                            </div>
                        @endif
                        
                        @if($perizinan->catatan_pembimbing)
                            <p class="text-sm {{ $perizinan->status == 'ditolak' ? 'text-red-600' : 'text-green-600' }} mb-2">
                                <strong>Catatan:</strong> {{ $perizinan->catatan_pembimbing }}
                            </p>
                        @endif
                        
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Diajukan: {{ $perizinan->created_at->format('d M Y, H:i') }}</span>
                            @if($perizinan->tanggal_approval)
                                <span class="{{ $perizinan->status_color }}">
                                    {{ ucfirst($perizinan->status) }}: {{ $perizinan->tanggal_approval->format('d M Y, H:i') }}
                                    @if($perizinan->pembimbing)
                                        oleh {{ $perizinan->pembimbing->nama_lengkap }}
                                    @endif
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($perizinans->hasPages())
                <div class="mt-6">
                    {{ $perizinans->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-8">
                <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Perizinan</h3>
                <p class="text-gray-600 mb-4">Anda belum mengajukan perizinan apapun</p>
                <button onclick="toggleForm()" class="bg-blue-600 text-white px-4 py-2 md:px-6 md:py-2 rounded-lg hover:bg-blue-700 text-sm md:text-base">
                    <i class="fas fa-plus mr-1 md:mr-2"></i>Ajukan Izin Pertama
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Modal Edit Perizinan (Outside main container) -->
<div id="editModal" class="fixed inset-0 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl border-2 border-gray-200 max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Edit Perizinan</h3>
                <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="editForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                <!-- Jenis -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Permohonan</label>
                    <select id="editJenis" name="jenis" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                    </select>
                </div>

                <!-- Tanggal -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" id="editTanggal" name="tanggal" min="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <!-- Keterangan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan/Keterangan</label>
                    <textarea id="editKeterangan" name="keterangan" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Jelaskan alasan izin/sakit dengan detail..." required></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-2 px-4 md:py-3 md:px-6 rounded-xl hover:bg-blue-700 text-sm md:text-base">
                        <i class="fas fa-save mr-1 md:mr-2"></i>
                        Simpan Perubahan
                    </button>
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 md:px-6 md:py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 text-sm md:text-base">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Perizinan (Outside main container) -->
<div id="detailModal" class="fixed inset-0 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl border-2 border-gray-200 max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Detail Perizinan</h3>
                <button onclick="closeDetailModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="detailContent" class="space-y-4">
                <!-- Content will be loaded here -->
            </div>
            
            <div class="mt-6">
                <button onclick="closeDetailModal()" class="w-full bg-gray-600 text-white py-2 md:py-3 rounded-xl hover:bg-gray-700 text-sm md:text-base">
                    Tutup
                </button>
            </div>
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
    </div> <!-- Close content-izin -->

    <!-- Tab Content: Pengajuan Presensi -->
    <div id="content-presensi" class="tab-content" style="display: none;">
        <!-- Button untuk menampilkan form -->
        <div class="mb-6">
            <button onclick="togglePresensiForm()" id="togglePresensiFormBtn" class="bg-orange-600 text-white font-bold py-3 px-6 rounded-xl hover:bg-orange-700">
                <i class="fas fa-plus mr-2"></i>Ajukan Presensi
            </button>
        </div>

        <!-- Form Pengajuan Presensi (Hidden by default) -->
        <div id="formPengajuanPresensi" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6" style="display: none;">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Ajukan Presensi</h2>
                <button onclick="togglePresensiForm()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-blue-800 mb-2">Syarat Pengajuan Presensi:</h3>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• Hanya dapat mengajukan presensi untuk <strong>3 hari terakhir</strong></li>
                            <li>• Khusus untuk <strong>lupa checkout</strong> atau <strong>presensi keliru</strong></li>
                            <li>• Wajib memberikan penjelasan yang detail</li>
                            <li>• Menunggu persetujuan dari pembimbing</li>
                        </ul>
                    </div>
                </div>
            </div>
        
            @if(session('success_presensi'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success_presensi') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form method="POST" action="{{ route('peserta.pengajuan-presensi.store') }}" enctype="multipart/form-data" class="space-y-4" id="presensiForm">
                @csrf
                
                <!-- Jenis Pengajuan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Pengajuan</label>
                    <select name="jenis_pengajuan" id="jenisPengajuan" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500" required>
                        <option value="">Pilih jenis pengajuan</option>
                        <option value="lupa_checkout" {{ old('jenis_pengajuan') == 'lupa_checkout' ? 'selected' : '' }}>Lupa Checkout</option>
                        <option value="presensi_keliru" {{ old('jenis_pengajuan') == 'presensi_keliru' ? 'selected' : '' }}>Presensi Keliru</option>
                    </select>
                </div>

                <!-- Tanggal -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Presensi</label>
                    <input type="date" name="tanggal_presensi" value="{{ old('tanggal_presensi') }}" 
                           min="{{ date('Y-m-d', strtotime('-3 days')) }}" 
                           max="{{ date('Y-m-d', strtotime('-1 day')) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500" required>
                    <p class="text-xs text-gray-500 mt-1">Hanya dapat memilih tanggal 3 hari terakhir</p>
                </div>

                <!-- Jam Masuk (untuk lupa checkout atau tidak presensi) -->
                <div id="jamMasukField">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jam Masuk</label>
                    <input type="time" name="jam_masuk" value="{{ old('jam_masuk') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika hanya mengajukan checkout</p>
                </div>

                <!-- Jam Keluar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jam Keluar</label>
                    <input type="time" name="jam_keluar" value="{{ old('jam_keluar') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500" required>
                </div>

                <!-- Penjelasan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Penjelasan Detail</label>
                    <textarea name="penjelasan" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="Jelaskan kronologi dan alasan mengapa perlu mengajukan presensi..." required>{{ old('penjelasan') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Minimal 20 karakter. Semakin detail penjelasan, semakin mudah disetujui</p>
                </div>

                <!-- Submit Button -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" class="flex-1 bg-orange-600 text-white font-bold py-2 px-4 md:py-3 md:px-6 rounded-xl hover:bg-orange-700 text-sm md:text-base">
                        <i class="fas fa-paper-plane mr-1 md:mr-2"></i>
                        Kirim Pengajuan
                    </button>
                    <button type="button" onclick="togglePresensiForm()" class="px-4 py-2 md:px-6 md:py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 text-sm md:text-base">
                        Batal
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistik Pengajuan Presensi -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-orange-600 text-sm"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-base font-bold text-gray-900" id="stat-pending">0</h3>
                        <p class="text-xs text-gray-600">Menunggu Persetujuan</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check text-green-600 text-sm"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-base font-bold text-gray-900" id="stat-disetujui">0</h3>
                        <p class="text-xs text-gray-600">Disetujui</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times text-red-600 text-sm"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-base font-bold text-gray-900" id="stat-ditolak">0</h3>
                        <p class="text-xs text-gray-600">Ditolak</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Pengajuan Presensi -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Pengajuan Presensi</h2>
            
            <div id="pengajuanPresensiList">
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clock text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-600 mb-2">Belum ada pengajuan presensi</p>
                    <p class="text-sm text-gray-500">Klik tombol "Ajukan Presensi" untuk membuat pengajuan pertama Anda</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Tab switching functionality
    function switchTab(tabName) {
        // Hide all tab contents
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => {
            content.style.display = 'none';
        });
        
        // Remove active class from all tab buttons
        const tabButtons = document.querySelectorAll('.tab-button');
        tabButtons.forEach(button => {
            button.classList.remove('active');
            button.classList.remove('border-gray-800', 'text-gray-800');
            button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-800', 'hover:border-gray-400');
        });
        
        // Show selected tab content
        document.getElementById('content-' + tabName).style.display = 'block';
        
        // Add active class to selected tab button
        const activeTab = document.getElementById('tab-' + tabName);
        activeTab.classList.add('active');
        activeTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-800', 'hover:border-gray-400');
        activeTab.classList.add('border-gray-800', 'text-gray-800');

        // Load data when switching to presensi tab
        if (tabName === 'presensi') {
            loadPengajuanPresensiData();
        }
    }

    // Toggle form pengajuan presensi
    function togglePresensiForm() {
        const form = document.getElementById('formPengajuanPresensi');
        const btn = document.getElementById('togglePresensiFormBtn');
        
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            btn.innerHTML = '<i class="fas fa-times mr-2"></i>Tutup Form';
            btn.classList.remove('bg-orange-600', 'hover:bg-orange-700');
            btn.classList.add('bg-gray-600', 'hover:bg-gray-700');
            
            // Scroll to form
            form.scrollIntoView({ behavior: 'smooth' });
        } else {
            form.style.display = 'none';
            btn.innerHTML = '<i class="fas fa-plus mr-2"></i>Ajukan Presensi';
            btn.classList.remove('bg-gray-600', 'hover:bg-gray-700');
            btn.classList.add('bg-orange-600', 'hover:bg-orange-700');
        }
    }

    // Load pengajuan presensi data
    function loadPengajuanPresensiData() {
        fetch('{{ route("peserta.pengajuan-presensi.data") }}')
            .then(response => response.json())
            .then(data => {
                // Update statistics
                document.getElementById('stat-pending').textContent = data.statistics.pending;
                document.getElementById('stat-disetujui').textContent = data.statistics.disetujui;
                document.getElementById('stat-ditolak').textContent = data.statistics.ditolak;

                // Update list
                const listContainer = document.getElementById('pengajuanPresensiList');
                if (data.pengajuans.length > 0) {
                    let html = '<div class="space-y-4">';
                    data.pengajuans.forEach(pengajuan => {
                        const statusColor = pengajuan.status === 'pending' ? 'yellow' : 
                                          pengajuan.status === 'disetujui' ? 'green' : 'red';
                        const statusIcon = pengajuan.status === 'pending' ? 'clock' :
                                         pengajuan.status === 'disetujui' ? 'check' : 'times';
                        
                        html += `
                            <div class="border border-gray-200 rounded-lg p-4 ${pengajuan.status === 'disetujui' ? 'border-green-200 bg-green-50' : pengajuan.status === 'ditolak' ? 'border-red-200 bg-red-50' : ''}">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-${statusColor}-100">
                                            <i class="fas fa-${statusIcon} text-${statusColor}-600"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-900">${pengajuan.jenis_pengajuan_display}</h3>
                                            <p class="text-sm text-gray-600">${new Date(pengajuan.tanggal_presensi).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-${statusColor}-100 text-${statusColor}-800">
                                        ${pengajuan.status_display}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <p class="text-sm text-gray-600">Jam Kerja:</p>
                                        <p class="font-medium">${pengajuan.jam_masuk || '-'} - ${pengajuan.jam_keluar}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Tanggal Pengajuan:</p>
                                        <p class="font-medium">${new Date(pengajuan.created_at).toLocaleDateString('id-ID')}</p>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <p class="text-sm text-gray-600 mb-1">Penjelasan:</p>
                                    <p class="text-sm bg-gray-50 p-2 rounded">${pengajuan.penjelasan}</p>
                                </div>
                                
                                ${pengajuan.keterangan_pembimbing ? `
                                    <div class="mb-3">
                                        <p class="text-sm text-gray-600 mb-1">Keterangan Pembimbing:</p>
                                        <p class="text-sm bg-blue-50 p-2 rounded border-l-4 border-blue-400">${pengajuan.keterangan_pembimbing}</p>
                                    </div>
                                ` : ''}
                                
                                <div class="flex justify-between items-center text-xs text-gray-500">
                                    <span>Diajukan ${new Date(pengajuan.created_at).toLocaleString('id-ID')}</span>
                                    ${pengajuan.status === 'pending' ? `
                                        <button onclick="deletePengajuan(${pengajuan.id})" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash mr-1"></i>Hapus
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    listContainer.innerHTML = html;
                } else {
                    listContainer.innerHTML = `
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-clock text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-600 mb-2">Belum ada pengajuan presensi</p>
                            <p class="text-sm text-gray-500">Klik tombol "Ajukan Presensi" untuk membuat pengajuan pertama Anda</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading data:', error);
            });
    }

    // Delete pengajuan
    function deletePengajuan(id) {
        if (confirm('Apakah Anda yakin ingin menghapus pengajuan ini?')) {
            const deleteUrl = '{{ route("peserta.pengajuan-presensi.destroy", ":id") }}'.replace(':id', id);
            
            fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) {
                    loadPengajuanPresensiData();
                    alert('Pengajuan berhasil dihapus');
                } else {
                    alert('Gagal menghapus pengajuan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus pengajuan');
            });
        }
    }

    // Handle jenis pengajuan change
    function handleJenisPengajuanChange() {
        const jenisSelect = document.getElementById('jenisPengajuan');
        const jamMasukField = document.getElementById('jamMasukField');
        const jamMasukInput = document.querySelector('input[name="jam_masuk"]');
        
        if (jenisSelect && jamMasukField) {
            jenisSelect.addEventListener('change', function() {
                if (this.value === 'lupa_checkout') {
                    jamMasukField.style.display = 'none';
                    jamMasukInput.required = false;
                    jamMasukInput.value = '';
                } else {
                    jamMasukField.style.display = 'block';
                    jamMasukInput.required = true;
                }
            });
        }
    }

    // Initialize everything
    document.addEventListener('DOMContentLoaded', function() {
        switchTab('izin'); // Show izin tab by default
        handleJenisPengajuanChange(); // Initialize presensi form logic
    });

    // Toggle form pengajuan
    function toggleForm() {
        const form = document.getElementById('formPengajuan');
        const btn = document.getElementById('toggleFormBtn');
        
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            btn.innerHTML = '<i class="fas fa-times mr-1 md:mr-2"></i><span class="hidden sm:inline">Tutup </span>Form';
            btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            btn.classList.add('bg-gray-600', 'hover:bg-gray-700');
            
            // Scroll to form
            form.scrollIntoView({ behavior: 'smooth' });
        } else {
            form.style.display = 'none';
            btn.innerHTML = '<i class="fas fa-plus mr-1 md:mr-2"></i><span class="hidden sm:inline">Ajukan </span>Izin';
            btn.classList.remove('bg-gray-600', 'hover:bg-gray-700');
            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }
    }

    // Edit perizinan modal
    function editPerizinan(id, jenis, tanggal, keterangan) {
        document.getElementById('editJenis').value = jenis;
        document.getElementById('editTanggal').value = tanggal;
        document.getElementById('editKeterangan').value = keterangan;
        document.getElementById('editForm').action = `/peserta/izin/${id}`;
        document.getElementById('editModal').classList.remove('hidden');
        
        // Add blur effect to main content
        document.querySelector('.space-y-6').style.filter = 'blur(2px)';
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        
        // Remove blur effect from main content
        document.querySelector('.space-y-6').style.filter = 'none';
    }

    // Detail perizinan modal
    function showDetail(id) {
        // Get data from existing row (we already have all data)
        const perizinanRow = event.target.closest('.border').querySelector('.font-medium').textContent;
        const perizinanData = @json($perizinans->items());
        const perizinan = perizinanData.find(p => p.id === id);
        
        if (perizinan) {
            const statusColor = perizinan.status === 'pending' ? 'text-yellow-600' : 
                               perizinan.status === 'disetujui' ? 'text-green-600' : 'text-red-600';
            
            const statusBg = perizinan.status === 'pending' ? 'bg-yellow-100' : 
                            perizinan.status === 'disetujui' ? 'bg-green-100' : 'bg-red-100';
            
            let content = `
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jenis Perizinan</label>
                        <p class="text-gray-900">${perizinan.jenis === 'izin' ? 'Izin' : 'Sakit'}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                        <p class="text-gray-900">${new Date(perizinan.tanggal).toLocaleDateString('id-ID', { 
                            day: 'numeric', 
                            month: 'long', 
                            year: 'numeric' 
                        })}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <span class="px-2 py-1 text-xs rounded-full font-medium ${statusColor} ${statusBg}">
                            ${perizinan.status === 'pending' ? 'Menunggu Persetujuan' : 
                              perizinan.status === 'disetujui' ? 'Disetujui' : 'Ditolak'}
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Keterangan</label>
                        <p class="text-gray-900">${perizinan.keterangan}</p>
                    </div>
            `;
            
            if (perizinan.catatan_pembimbing) {
                content += `
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Catatan Pembimbing</label>
                        <p class="${perizinan.status === 'ditolak' ? 'text-red-600' : 'text-green-600'}">${perizinan.catatan_pembimbing}</p>
                    </div>
                `;
            }
            
            content += `
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Pengajuan</label>
                        <p class="text-gray-900">${new Date(perizinan.created_at).toLocaleDateString('id-ID', { 
                            day: 'numeric', 
                            month: 'long', 
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })}</p>
                    </div>
            `;
            
            if (perizinan.tanggal_approval) {
                content += `
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal ${perizinan.status === 'disetujui' ? 'Persetujuan' : 'Penolakan'}</label>
                        <p class="text-gray-900">${new Date(perizinan.tanggal_approval).toLocaleDateString('id-ID', { 
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
            
            document.getElementById('detailContent').innerHTML = content;
            document.getElementById('detailModal').classList.remove('hidden');
            
            // Add blur effect to main content
            document.querySelector('.space-y-6').style.filter = 'blur(2px)';
        }
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
        
        // Remove blur effect from main content
        document.querySelector('.space-y-6').style.filter = 'none';
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

        // Show form if there are validation errors
        @if($errors->any())
            toggleForm();
        @endif

        // Add event listener for presensi tab
        document.getElementById('tab-presensi').addEventListener('click', function() {
            setTimeout(() => {
                loadPengajuanPresensiData();
            }, 100);
        });

        // Show success message and hide form
        @if(session('success'))
            const form = document.getElementById('formPengajuan');
            if (form.style.display === 'block') {
                setTimeout(() => {
                    toggleForm();
                }, 3000); // Hide form after 3 seconds
            }
        @endif
    });

    // File upload functionality
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('buktiDokumen');
    const uploadContent = document.getElementById('uploadContent');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');

    // Click to upload
    dropZone.addEventListener('click', function() {
        fileInput.click();
    });

    // Drag and drop
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });

    // File input change
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleFile(e.target.files[0]);
        }
    });

    function handleFile(file) {
        console.log('File selected:', file.name, file.type, file.size);
        
        // Validate file type
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            alert('Tipe file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.');
            console.log('File type rejected:', file.type);
            return;
        }

        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 2MB.');
            console.log('File size rejected:', file.size);
            return;
        }

        console.log('File validated successfully');
        
        // Update UI
        fileName.textContent = file.name;
        fileSize.textContent = `(${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        
        uploadContent.classList.add('hidden');
        filePreview.classList.remove('hidden');
        
        console.log('UI updated, file input value:', fileInput.files.length);
    }

    function removeFile() {
        fileInput.value = '';
        uploadContent.classList.remove('hidden');
        filePreview.classList.add('hidden');
    }

    // Make removeFile function global
    window.removeFile = removeFile;

    // Functions for bukti dokumen modal
    function showBuktiDokumen(url, type) {
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

    // Make functions global
    window.showBuktiDokumen = showBuktiDokumen;
    window.closeBuktiDokumenModal = closeBuktiDokumenModal;

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

    // Debug form submission
    const perizinanForm = document.getElementById('perizinanForm');
    if (perizinanForm) {
        perizinanForm.addEventListener('submit', function(e) {
            console.log('Form submitting...');
            console.log('File input has files:', fileInput.files.length);
            if (fileInput.files.length > 0) {
                console.log('File details:', {
                    name: fileInput.files[0].name,
                    size: fileInput.files[0].size,
                    type: fileInput.files[0].type
                });
            }
            
            // Check form data
            const formData = new FormData(perizinanForm);
            console.log('FormData entries:');
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }
        });
    }
</script>
@endpush
