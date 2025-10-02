@extends('layouts.peserta')

@section('title', 'Kegiatan Harian | Day-In')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="space-y-6 p-4">
    <!-- Kegiatan Harian Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 mb-2">Kegiatan Harian</h1>
                <p class="text-sm text-gray-600">Catat aktivitas dan pembelajaran harian Anda</p>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-6 mt-4 sm:mt-0">
                <button onclick="toggleForm()" id="toggleBtn" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 text-sm transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i>Tambah Kegiatan
                </button>
                <button onclick="exportPdf()" class="bg-red-600 text-white px-6 py-2.5 rounded-lg hover:bg-red-700 text-sm transition-colors duration-200">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </button>
            </div>
        </div>
        
        <!-- Form Tambah Kegiatan -->
        <form id="kegiatanForm" class="hidden space-y-4 mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200" action="{{ route('peserta.kegiatan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Tanggal -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Jam Mulai -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jam Mulai</label>
                    <input type="time" name="jam_mulai" value="{{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Jam Selesai -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jam Selesai </label>
                    <input type="time" name="jam_selesai"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Kategori Aktivitas -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori Aktivitas</label>
                <select name="kategori_aktivitas" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Pilih Kategori...</option>
                    <option value="meeting">Meeting</option>
                    <option value="pengerjaan_tugas">Pengerjaan Tugas</option>
                    <option value="dokumentasi">Dokumentasi</option>
                    <option value="laporan">Laporan</option>
                </select>
            </div>

            <!-- Judul Kegiatan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Judul Kegiatan</label>
                <input type="text" name="judul" required placeholder="Masukkan judul kegiatan..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Deskripsi Kegiatan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Kegiatan</label>
                <textarea name="deskripsi" rows="4" required placeholder="Jelaskan detail kegiatan yang dilakukan..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>

            <!-- Upload Bukti -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Upload Bukti (Optional)</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4" id="fileUpload">
                    <div class="text-center">
                        <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-2"></i>
                        <p class="text-sm text-gray-600 mb-2">Klik untuk upload file atau drag & drop</p>
                        <p class="text-xs text-gray-500">Foto, PDF, DOC, DOCX (Max 5MB)</p>
                        <input type="file" name="bukti" class="hidden" accept="image/*,.pdf,.doc,.docx" id="buktiInput">
                    </div>
                </div>
                <div id="filePreview" class="hidden mt-3"></div>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 bg-gradient-to-r from-green-600 to-green-700 text-white font-bold py-3 px-6 rounded-lg hover:from-green-700 hover:to-green-800 transition-all duration-200">
                    <i class="fas fa-save mr-2"></i>Simpan Kegiatan
                </button>
                <button type="button" onclick="cancelForm()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    Batal
                </button>
            </div>
        </form>
    </div>

    <!-- Filter & Daftar Kegiatan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Kegiatan</h2>
        </div>
        
        <!-- Filter & Search -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" placeholder="Cari kegiatan..." id="searchInput"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <select id="filterKategori" class="w-full sm:w-auto px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Kategori</option>
                        <option value="meeting">Meeting</option>
                        <option value="pengerjaan_tugas">Pengerjaan Tugas</option>
                        <option value="dokumentasi">Dokumentasi</option>
                        <option value="laporan">Laporan</option>
                    </select>
                    <select id="filterBulan" class="w-full sm:w-auto px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Bulan</option>
                        <option value="01">Januari</option>
                        <option value="02">Februari</option>
                        <option value="03">Maret</option>
                        <option value="04">April</option>
                        <option value="05">Mei</option>
                        <option value="06">Juni</option>
                        <option value="07">Juli</option>
                        <option value="08">Agustus</option>
                        <option value="09" selected>September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Daftar Kegiatan -->
        
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kegiatan & Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bukti</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="kegiatanTableBody">
                    @php 
                        $groupedKegiatans = $kegiatans->groupBy(function($kegiatan) {
                            return \Carbon\Carbon::parse($kegiatan->tanggal)->format('Y-m-d');
                        })->sortKeys();
                    @endphp
                    
                    @forelse($groupedKegiatans as $tanggal => $kegiatanGroup)
                        @foreach($kegiatanGroup as $index => $kegiatan)
                        <tr class="hover:bg-gray-50 {{ $index > 0 ? 'border-t-0' : '' }}">
                            <!-- Tanggal - hanya tampil di row pertama -->
                            @if($index === 0)
                                <td rowspan="{{ $kegiatanGroup->count() }}" class="px-6 py-4 whitespace-nowrap bg-gray-50 border-r border-gray-200">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ Carbon\Carbon::parse($kegiatan->tanggal)->format('d M Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ Carbon\Carbon::parse($kegiatan->tanggal)->locale('id')->isoFormat('dddd') }}
                                    </div>
                                </td>
                            @endif
                            
                            <!-- Waktu -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $kegiatan->formatted_jam_mulai }}
                                    @if($kegiatan->jam_selesai)
                                        - {{ $kegiatan->formatted_jam_selesai }}
                                    @endif
                                </div>
                            </td>
                            
                            <!-- Kegiatan & Kategori -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $kegiatan->judul }}</div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $kegiatan->formatted_kategori_aktivitas }}
                                    </span>
                                </div>
                            </td>
                            
                            <!-- Deskripsi -->
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs">
                                    {{ Str::limit($kegiatan->deskripsi, 200) }}
                                </div>
                            </td>
                            
                            <!-- Bukti -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($kegiatan->bukti)
                                    <div class="flex items-center space-x-2">
                                    <a href="{{ asset('storage/' . $kegiatan->bukti) }}" 
                                        target="_blank" 
                                        class="flex items-center space-x-2 text-blue-600 hover:underline">
                                        @if($kegiatan->is_bukti_image)
                                            <i class="fas fa-file-image text-blue-600"></i>
                                        @elseif($kegiatan->bukti_file_type == 'pdf')
                                            <i class="fas fa-file-pdf text-red-600"></i>
                                        @else
                                            <i class="fas fa-file-word text-blue-600"></i>
                                        @endif
                                        <span class="text-sm text-gray-600">{{ $kegiatan->bukti_file_name }}</span>
                                    </a>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm">Tidak ada</span>
                                @endif
                            </td>
                            
                            <!-- Aksi -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button onclick="viewDetail({{ $kegiatan->id }})" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editKegiatan({{ $kegiatan->id }})" class="text-green-600 hover:text-green-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteKegiatan({{ $kegiatan->id }})" class="text-red-600 hover:text-red-900" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-clipboard-list text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Kegiatan</h3>
                                <p class="text-gray-600">Mulai tambahkan kegiatan harian Anda</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-6" id="kegiatanMobileList">
            @php 
                $groupedKegiatansMobile = $kegiatans->groupBy(function($kegiatan) {
                    return \Carbon\Carbon::parse($kegiatan->tanggal)->format('Y-m-d');
                })->sortKeys();
            @endphp
            
            @forelse($groupedKegiatansMobile as $tanggal => $kegiatanGroup)
                <!-- Header Tanggal -->
                <div class="bg-gray-50 rounded-lg p-3 border-l-4 border-blue-500">
                    <h3 class="font-semibold text-gray-900">
                        {{ Carbon\Carbon::parse($kegiatanGroup->first()->tanggal)->format('d M Y') }}
                    </h3>
                    <p class="text-sm text-gray-500">
                        {{ Carbon\Carbon::parse($kegiatanGroup->first()->tanggal)->locale('id')->isoFormat('dddd') }}
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
                            <div class="flex items-center space-x-2">
                                <button onclick="viewDetail({{ $kegiatan->id }})" class="text-blue-600 hover:text-blue-900 p-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editKegiatan({{ $kegiatan->id }})" class="text-green-600 hover:text-green-900 p-1">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteKegiatan({{ $kegiatan->id }})" class="text-red-600 hover:text-red-900 p-1">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="text-sm text-gray-700 mb-3">{{ Str::limit($kegiatan->deskripsi, 100) }}</div>
                        
                        <div class="flex items-center justify-between">
                            @if($kegiatan->bukti)
                                <div class="flex items-center space-x-2">
                                    @if($kegiatan->is_bukti_image)
                                        <i class="fas fa-file-image text-blue-600"></i>
                                    @elseif($kegiatan->bukti_file_type == 'pdf')
                                        <i class="fas fa-file-pdf text-red-600"></i>
                                    @else
                                        <i class="fas fa-file-word text-blue-600"></i>
                                    @endif
                                    <span class="text-sm text-gray-600">{{ $kegiatan->bukti_file_name }}</span>
                                </div>
                            @else
                                <span class="text-gray-400 text-sm">Tidak ada bukti</span>
                            @endif
                            <button onclick="viewDetail({{ $kegiatan->id }})" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            @empty
            <div class="text-center py-8">
                <i class="fas fa-clipboard-list text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Kegiatan</h3>
                <p class="text-gray-600">Mulai tambahkan kegiatan harian Anda</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($kegiatans->hasPages())
        <div class="mt-6">
            {{ $kegiatans->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Detail Kegiatan -->
<div id="detailModal" class="fixed inset-0 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl border-2 border-gray-200 max-w-2xl w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Detail Kegiatan</h3>
                <button onclick="closeDetailModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="detailContent" class="space-y-4">
                <!-- Content will be loaded here -->
            </div>
            
            <div class="mt-6 flex gap-3">
                <button onclick="closeDetailModal()" class="flex-1 bg-gray-600 text-white py-3 rounded-lg hover:bg-gray-700">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle form visibility
    function toggleForm() {
        const form = document.getElementById('kegiatanForm');
        const btn = document.getElementById('toggleBtn');
        
        if (form.classList.contains('hidden')) {
            form.classList.remove('hidden');
            btn.innerHTML = '<i class="fas fa-minus mr-2"></i>Tutup Form';
            btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            btn.classList.add('bg-gray-600', 'hover:bg-gray-700');
        } else {
            form.classList.add('hidden');
            btn.innerHTML = '<i class="fas fa-plus mr-2"></i>Tambah Kegiatan';
            btn.classList.remove('bg-gray-600', 'hover:bg-gray-700');
            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }
    }

    // Cancel form
    function cancelForm() {
        document.getElementById('kegiatanForm').reset();
        toggleForm();
        removeFilePreview();
    }

    // File upload handling
    const fileUpload = document.getElementById('fileUpload');
    const fileInput = document.getElementById('buktiInput');
    const filePreview = document.getElementById('filePreview');
    
    fileUpload.addEventListener('click', function() {
        fileInput.click();
    });
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            showFilePreview(file);
        }
    });

    function showFilePreview(file) {
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        const fileType = file.type;
        let icon = 'fas fa-file';
        
        if (fileType.includes('image')) {
            icon = 'fas fa-file-image text-blue-600';
        } else if (fileType.includes('pdf')) {
            icon = 'fas fa-file-pdf text-red-600';
        } else if (fileType.includes('word')) {
            icon = 'fas fa-file-word text-blue-600';
        }
        
        filePreview.innerHTML = `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <i class="${icon} text-lg"></i>
                    <div>
                        <p class="text-sm font-medium text-gray-900">${file.name}</p>
                        <p class="text-xs text-gray-500">${fileSize} MB</p>
                    </div>
                </div>
                <button type="button" onclick="removeFilePreview()" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        filePreview.classList.remove('hidden');
    }

    function removeFilePreview() {
        filePreview.classList.add('hidden');
        fileInput.value = '';
    }

    // Form submission
    document.getElementById('kegiatanForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        const editId = this.getAttribute('data-edit-id');
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
        submitBtn.disabled = true;
        
        // Determine if this is an edit or create
        const isEdit = editId && editId !== '';
        const url = isEdit ? 
            `{{ route("peserta.kegiatan.update", ":id") }}`.replace(':id', editId) : 
            '{{ route("peserta.kegiatan.store") }}';
        const method = isEdit ? 'PUT' : 'POST';
        
        // For PUT requests, we need to add the method override
        if (isEdit) {
            formData.append('_method', 'PUT');
        }
        
        fetch(url, {
            method: 'POST', // Always POST for FormData with Laravel
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showAlert('success', data.message);
                
                // Reset form and hide it
                this.reset();
                this.removeAttribute('data-edit-id');
                removeFilePreview();
                toggleForm();
                
                // Reset button text
                submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Simpan Kegiatan';
                
                // Reload the page to show new data
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Terjadi kesalahan saat menyimpan kegiatan.');
        })
        .finally(() => {
            // Restore button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Search and filter functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        filterKegiatan();
    });

    document.getElementById('filterBulan').addEventListener('change', function() {
        filterKegiatan();
    });

    document.getElementById('filterKategori').addEventListener('change', function() {
        filterKegiatan();
    });

    function filterKegiatan() {
        const search = document.getElementById('searchInput').value;
        const month = document.getElementById('filterBulan').value;
        const kategori = document.getElementById('filterKategori').value;
        
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (month) params.append('month', month);
        if (kategori) params.append('kategori', kategori);
        
        fetch(`{{ route("peserta.kegiatan.search") }}?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateKegiatanList(data.data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function updateKegiatanList(kegiatans) {
        const tableBody = document.getElementById('kegiatanTableBody');
        const mobileList = document.getElementById('kegiatanMobileList');
        
        if (kegiatans.length === 0) {
            // Show empty state in table
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center">
                        <div class="text-gray-500">
                            <i class="fas fa-clipboard-list text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Kegiatan</h3>
                            <p class="text-gray-600">Mulai tambahkan kegiatan harian Anda</p>
                        </div>
                    </td>
                </tr>
            `;
            
            // Show empty state in mobile
            mobileList.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-clipboard-list text-gray-400 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Kegiatan</h3>
                    <p class="text-gray-600">Mulai tambahkan kegiatan harian Anda</p>
                </div>
            `;
            return;
        }
        
        // Group kegiatans by date
        const groupedKegiatans = {};
        kegiatans.forEach(kegiatan => {
            const date = kegiatan.tanggal;
            if (!groupedKegiatans[date]) {
                groupedKegiatans[date] = [];
            }
            groupedKegiatans[date].push(kegiatan);
        });
        
        // Update desktop table with grouping
        let tableHtml = '';
        Object.keys(groupedKegiatans).sort().forEach((date, dateIndex) => {
            const kegiatanGroup = groupedKegiatans[date];
            kegiatanGroup.forEach((kegiatan, index) => {
                const buktiIcon = getBuktiIcon(kegiatan.bukti_type);
                tableHtml += `
                    <tr class="hover:bg-gray-50">
                        ${index === 0 ? `
                            <td class="px-6 py-4 whitespace-nowrap border-r text-center" rowspan="${kegiatanGroup.length}">
                                <div class="text-sm font-medium text-gray-900">${new Date(kegiatan.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}</div>
                                <div class="text-xs text-gray-500">${new Date(kegiatan.tanggal).toLocaleDateString('id-ID', { weekday: 'long' })}</div>
                            </td>
                        ` : ''}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${kegiatan.jam_mulai}${kegiatan.jam_selesai ? ' - ' + kegiatan.jam_selesai : ''}</div>
                            ${kegiatan.duration ? `<div class="text-xs text-gray-500">${kegiatan.duration}</div>` : ''}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">${kegiatan.judul}</div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ${kegiatan.kategori_aktivitas}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 max-w-xs">${kegiatan.deskripsi}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            ${kegiatan.bukti ? `
                                <div class="flex items-center space-x-2">
                                    <i class="${buktiIcon}"></i>
                                    <span class="text-sm text-gray-600">${kegiatan.bukti}</span>
                                </div>
                            ` : '<span class="text-gray-400 text-sm">Tidak ada</span>'}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <button onclick="viewDetail(${kegiatan.id})" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editKegiatan(${kegiatan.id})" class="text-green-600 hover:text-green-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteKegiatan(${kegiatan.id})" class="text-red-600 hover:text-red-900" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        });
        tableBody.innerHTML = tableHtml;
        
        // Update mobile cards with grouping
        let mobileHtml = '';
        Object.keys(groupedKegiatans).sort().forEach((date) => {
            const kegiatanGroup = groupedKegiatans[date];
            const firstKegiatan = kegiatanGroup[0];
            
            mobileHtml += `
                <!-- Header Tanggal -->
                <div class="bg-gray-50 rounded-lg p-3 border-l-4 border-blue-500">
                    <h3 class="font-semibold text-gray-900">
                        ${new Date(firstKegiatan.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}
                    </h3>
                    <p class="text-sm text-gray-500">
                        ${new Date(firstKegiatan.tanggal).toLocaleDateString('id-ID', { weekday: 'long' })}
                        • ${kegiatanGroup.length} kegiatan
                    </p>
                </div>
                
                <!-- Kegiatan Cards -->
                <div class="space-y-3 ml-4">
            `;
            
            kegiatanGroup.forEach(kegiatan => {
                const buktiIcon = getBuktiIcon(kegiatan.bukti_type);
                mobileHtml += `
                    <div class="border border-gray-200 rounded-lg p-4 bg-white">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h4 class="font-medium text-gray-900">${kegiatan.judul}</h4>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        ${kegiatan.kategori_aktivitas}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
                                    <i class="fas fa-clock"></i>
                                    <span>
                                        ${kegiatan.jam_mulai}${kegiatan.jam_selesai ? ' - ' + kegiatan.jam_selesai : ''}
                                    </span>
                                    ${kegiatan.duration ? `<span class="text-xs text-gray-400">(${kegiatan.duration})</span>` : ''}
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="viewDetail(${kegiatan.id})" class="text-blue-600 hover:text-blue-900 p-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editKegiatan(${kegiatan.id})" class="text-green-600 hover:text-green-900 p-1">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteKegiatan(${kegiatan.id})" class="text-red-600 hover:text-red-900 p-1">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="text-sm text-gray-700 mb-3">${kegiatan.deskripsi}</div>
                        
                        <div class="flex items-center justify-between">
                            ${kegiatan.bukti ? `
                                <div class="flex items-center space-x-2">
                                    <i class="${buktiIcon}"></i>
                                    <span class="text-sm text-gray-600">${kegiatan.bukti}</span>
                                </div>
                            ` : '<span class="text-gray-400 text-sm">Tidak ada bukti</span>'}
                            <button onclick="viewDetail(${kegiatan.id})" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                `;
            });
            
            mobileHtml += `</div>`;
        });
        
        mobileList.innerHTML = mobileHtml;
    }

    function getBuktiIcon(fileType) {
        switch (fileType) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                return 'fas fa-file-image text-blue-600';
            case 'pdf':
                return 'fas fa-file-pdf text-red-600';
            case 'doc':
            case 'docx':
                return 'fas fa-file-word text-blue-600';
            default:
                return 'fas fa-file text-gray-600';
        }
    }

    // View detail
    function viewDetail(id) {
        fetch(`{{ route("peserta.kegiatan.show", ":id") }}`.replace(':id', id))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('detailContent').innerHTML = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                                <p class="text-gray-900">${data.data.tanggal}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jam</label>
                                <p class="text-gray-900">
                                    ${data.data.jam_mulai} - ${data.data.jam_selesai || 'Belum selesai'}
                                </p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Judul Kegiatan</label>
                            <p class="text-gray-900 font-medium">${data.data.judul}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <p class="text-gray-900">${data.data.deskripsi}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Bukti</label>
                            ${data.data.bukti ? `
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-file text-blue-600"></i>
                                    <a href="${data.data.bukti_url}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">${data.data.bukti}</a>
                                </div>
                            ` : '<p class="text-gray-500">Tidak ada bukti</p>'}
                        </div>
                    </div>
                `;
                document.getElementById('detailModal').classList.remove('hidden');
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Terjadi kesalahan saat mengambil detail kegiatan.');
        });
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }

    function editKegiatan(id) {
        // Get kegiatan data first
        fetch(`{{ route("peserta.kegiatan.edit", ":id") }}`.replace(':id', id))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate form with data
                document.querySelector('input[name="tanggal"]').value = data.data.tanggal;
                document.querySelector('input[name="jam_mulai"]').value = data.data.jam_mulai;
                document.querySelector('input[name="jam_selesai"]').value = data.data.jam_selesai || '';
                document.querySelector('input[name="judul"]').value = data.data.judul;
                document.querySelector('textarea[name="deskripsi"]').value = data.data.deskripsi;
                document.querySelector('select[name="kategori_aktivitas"]').value = data.data.kategori_aktivitas;
                
                // Show form if hidden
                if (document.getElementById('kegiatanForm').classList.contains('hidden')) {
                    toggleForm();
                }
                
                // Change form to edit mode
                const form = document.getElementById('kegiatanForm');
                form.setAttribute('data-edit-id', id);
                form.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-save mr-2"></i>Update Kegiatan';
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Terjadi kesalahan saat mengambil data kegiatan.');
        });
    }

    function deleteKegiatan(id) {
        if (confirm('Apakah Anda yakin ingin menghapus kegiatan ini?')) {
            fetch(`{{ route("peserta.kegiatan.destroy", ":id") }}`.replace(':id', id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Terjadi kesalahan saat menghapus kegiatan.');
            });
        }
    }

    function exportPdf() {
        // Open PDF in new window
        window.open('{{ route("peserta.kegiatan.export.pdf") }}', '_blank');
    }

    // Alert function
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-4 right-4 p-4 rounded-lg text-white z-50 ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
        alertDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'} mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }

    // Close modal when clicking outside
    document.getElementById('detailModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDetailModal();
        }
    });
</script>
@endpush
