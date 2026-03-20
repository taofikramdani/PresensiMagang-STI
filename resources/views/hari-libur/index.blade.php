@extends('layouts.main')

@section('title', 'Manajemen Hari Libur')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Hari Libur Nasional</h1>
                <p class="text-gray-600 mt-1">Kelola hari libur </p>
            </div>
            <div class="flex space-x-3">
                <button id="btnTambahLibur" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Hari Libur
                </button>
                <button id="btnSyncAPI" 
                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-md transition-colors duration-200">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Sinkronisasi dari API
                </button>
                <button id="btnImportLibur" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-md transition-colors duration-200">
                    <i class="fas fa-file-import mr-2"></i>
                    Import dari JSON
                </button>
            </div>
        </div>
    </div>

    <!-- Legend Section -->
    <div class="bg-white rounded-lg shadow-sm p-4">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Keterangan Warna:</h3>
        <div class="flex flex-wrap gap-4">
            <div class="flex items-center">
                <div class="w-4 h-4 bg-red-500 rounded mr-2"></div>
                <span class="text-sm text-gray-600">Hari Libur Nasional</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                <span class="text-sm text-gray-600">Hari Libur Keagamaan</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-blue-500 rounded mr-2"></div>
                <span class="text-sm text-gray-600">Hari Libur Custom</span>
            </div>
        </div>
    </div>

    <!-- Calendar Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div id="calendar"></div>
    </div>
</div>

<!-- Modal Tambah/Edit Hari Libur -->
<div id="modalHariLibur" class="fixed inset-0 items-center justify-center hidden z-50 p-4" style="backdrop-filter: blur(2px); background: rgba(0, 0, 0, 0.3);">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="p-4">
            <!-- Modal Header -->
            <div class="flex justify-between items-center pb-2 border-b">
                <h3 class="text-base font-medium text-gray-900" id="modalTitle">Tambah Hari Libur</h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="formHariLibur" class="mt-3 space-y-3">
                <input type="hidden" id="hariLiburId" name="id">
                
                <div>
                    <label for="nama_libur" class="block text-xs font-medium text-gray-700 mb-1">
                        Nama Hari Libur *
                    </label>
                    <input type="text" 
                           id="nama_libur" 
                           name="nama_libur" 
                           required
                           class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Contoh: Hari Kemerdekaan Indonesia">
                </div>

                <div>
                    <label for="tanggal" class="block text-xs font-medium text-gray-700 mb-1">
                        Tanggal *
                    </label>
                    <input type="date" 
                           id="tanggal" 
                           name="tanggal" 
                           required
                           class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="jenis" class="block text-xs font-medium text-gray-700 mb-1">
                        Jenis Hari Libur *
                    </label>
                    <select id="jenis" 
                            name="jenis" 
                            required
                            class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Jenis</option>
                        <option value="nasional">Hari Libur Nasional</option>
                        <option value="keagamaan">Hari Libur Keagamaan</option>
                        <option value="custom">Hari Libur Custom</option>
                    </select>
                </div>

                <div>
                    <label for="warna" class="block text-xs font-medium text-gray-700 mb-1">
                        Warna pada Kalender *
                    </label>
                    <div class="flex space-x-2">
                        <input type="color" 
                               id="warna" 
                               name="warna" 
                               value="#ff0000"
                               class="w-8 h-6 border border-gray-300 rounded cursor-pointer">
                        <input type="text" 
                               id="warnaText" 
                               value="#ff0000"
                               class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="#ff0000">
                    </div>
                </div>

                <div>
                    <label for="deskripsi" class="block text-xs font-medium text-gray-700 mb-1">
                        Deskripsi
                    </label>
                    <textarea id="deskripsi" 
                              name="deskripsi" 
                              rows="2"
                              class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Deskripsi hari libur (opsional)"></textarea>
                </div>

                <!-- Modal Actions -->
                <div class="flex justify-end space-x-2 pt-3 border-t">
                    <button type="button" 
                            id="btnBatal" 
                            class="px-3 py-1 text-xs bg-gray-300 hover:bg-gray-400 text-gray-700 rounded transition-colors duration-200">
                        Batal
                    </button>
                    <button type="submit" 
                            id="btnSimpan"
                            class="px-3 py-1 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors duration-200">
                        <i class="fas fa-save mr-1"></i>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Sinkronisasi API -->
<div id="modalSyncAPI" class="fixed inset-0 items-center justify-center hidden z-50 p-4" style="backdrop-filter: blur(8px); background: rgba(0, 0, 0, 0.3);">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">
        <div class="p-6">
            <!-- Modal Header -->
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-cloud-download-alt text-purple-600 mr-2"></i>
                    Sinkronisasi Hari Libur dari API
                </h3>
                <button id="closeSyncModal" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="mt-6">
                <p class="text-sm text-gray-600 mb-4">
                    Sinkronisasi data hari libur nasional Indonesia dari API resmi 
                    <a href="https://api-harilibur.vercel.app/" target="_blank" class="text-blue-600 hover:underline">
                        api-harilibur.vercel.app
                    </a>
                </p>

                <form id="formSyncAPI" class="space-y-4">
                    <div>
                        <label for="sync_year" class="block text-sm font-medium text-gray-700 mb-1">
                            Tahun
                        </label>
                        <select id="sync_year" name="year" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Pilih Tahun</option>
                        </select>
                    </div>

                    <div>
                        <label for="sync_month" class="block text-sm font-medium text-gray-700 mb-1">
                            Bulan (Opsional)
                        </label>
                        <select id="sync_month" name="month"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Semua Bulan</option>
                            <option value="1">Januari</option>
                            <option value="2">Februari</option>
                            <option value="3">Maret</option>
                            <option value="4">April</option>
                            <option value="5">Mei</option>
                            <option value="6">Juni</option>
                            <option value="7">Juli</option>
                            <option value="8">Agustus</option>
                            <option value="9">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Kosongkan untuk mengambil semua bulan</p>
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    Data yang sudah ada dengan jenis "custom" tidak akan ditimpa. 
                                    Data nasional/keagamaan akan diperbarui jika sudah ada.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" id="btnCancelSync"
                                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-md transition-colors duration-200">
                            Batal
                        </button>
                        <button type="submit" id="btnDoSync"
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition-colors duration-200">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Sinkronisasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Import Hari Libur -->
<div id="modalImport" class="fixed inset-0 items-center justify-center hidden z-50 p-4" style="backdrop-filter: blur(8px); background: rgba(0, 0, 0, 0.3);">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <!-- Modal Header -->
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Import Hari Libur dari File JSON</h3>
                <button id="closeImportModal" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Import Options -->
            <div class="mt-6">
                <!-- Import from File -->
                <div class="border rounded-lg p-6">
                    <h4 class="font-medium text-gray-900 mb-3">
                        <i class="fas fa-file-upload text-blue-500 mr-2"></i>
                        Import dari File JSON
                    </h4>
                    <p class="text-sm text-gray-600 mb-4">
                        Upload file JSON dengan format yang sesuai.
                    </p>
                    
                    <!-- Download Template Button -->
                    <div class="mb-4 space-y-2">
                        <div>
                            <a href="{{ route('admin.hari-libur.template.download') }}" 
                               class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-md transition-colors duration-200 border">
                                <i class="fas fa-download mr-2"></i>
                                Download Template JSON
                            </a>
                        </div>
                        <div class="text-xs text-gray-500">
                            Atau <a href="{{ asset('template_hari_libur.json') }}" 
                                   download="template_hari_libur.json" 
                                   class="text-blue-600 hover:text-blue-800 underline">
                                download langsung dari sini
                            </a>
                        </div>
                    </div>
                    
                    <form id="formImportFile" class="space-y-4">
                        <div>
                            <label for="import_file" class="block text-sm font-medium text-gray-700 mb-1">
                                Pilih File JSON
                            </label>
                            <input type="file" 
                                   id="import_file" 
                                   name="import_file" 
                                   accept=".json,.txt"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Format: JSON, maksimal 2MB</p>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="replace_existing_file" name="replace_existing" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="replace_existing_file" class="ml-2 block text-sm text-gray-700">
                                Timpa data yang sudah ada
                            </label>
                        </div>
                        
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors duration-200">
                            <i class="fas fa-upload mr-2"></i>
                            Import File
                        </button>
                    </form>

                    <!-- Format Example -->
                    <div class="mt-4 bg-gray-50 rounded-lg p-4">
                        <h5 class="font-medium text-gray-900 mb-2">Format File JSON:</h5>
                        <pre class="text-xs bg-gray-100 p-3 rounded overflow-x-auto"><code>[
  {
    "nama_libur": "Hari Kemerdekaan RI",
    "tanggal": "2025-08-17",
    "deskripsi": "Hari Kemerdekaan Indonesia ke-80",
    "jenis": "nasional",
    "warna": "#e74c3c",
    "is_active": true
  }
]</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Helper function to get CSRF token
    function getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        // Fallback: get from Laravel global variable if available
        return window.Laravel && window.Laravel.csrfToken ? window.Laravel.csrfToken : '';
    }

    const calendarEl = document.getElementById('calendar');
    const modal = document.getElementById('modalHariLibur');
    const form = document.getElementById('formHariLibur');
    const modalTitle = document.getElementById('modalTitle');
    
    // Initialize FullCalendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridYear'
        },
        height: 'auto',
        events: {
            url: '{{ route("admin.hari-libur.events") }}',
            failure: function() {
                Swal.fire('Error', 'Gagal memuat data hari libur', 'error');
            }
        },
        eventClick: function(info) {
            showEventDetails(info.event);
        },
        dateClick: function(info) {
            openModal('add', info.dateStr);
        },
        eventDidMount: function(info) {
            // Add tooltip
            info.el.setAttribute('title', info.event.extendedProps.description || info.event.title);
        }
    });

    calendar.render();

    // Modal functions
    function openModal(mode = 'add', date = null, eventData = null) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        form.reset();
        
        if (mode === 'add') {
            modalTitle.textContent = 'Tambah Hari Libur';
            document.getElementById('hariLiburId').value = '';
            if (date) {
                document.getElementById('tanggal').value = date;
            }
            // Set default colors based on type
            setDefaultColor();
        } else if (mode === 'edit') {
            modalTitle.textContent = 'Edit Hari Libur';
            fillFormData(eventData);
        }
    }
    
    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        form.reset();
    }
    
    function fillFormData(eventData) {
        console.log('Filling form with event data:', eventData);
        
        document.getElementById('hariLiburId').value = eventData.id;
        document.getElementById('nama_libur').value = eventData.title || '';
        
        // Format tanggal dengan benar untuk input date
        let tanggal = eventData.startStr;
        if (eventData.start) {
            tanggal = new Date(eventData.start).toISOString().split('T')[0];
        }
        document.getElementById('tanggal').value = tanggal;
        
        // Handle jenis dengan fallback
        const jenis = eventData.extendedProps?.jenis || 'nasional';
        document.getElementById('jenis').value = jenis;
        
        // Handle warna dengan multiple fallback
        const warna = eventData.color || 
                     eventData.backgroundColor || 
                     eventData.extendedProps?.warna || 
                     '#ff0000';
        document.getElementById('warna').value = warna;
        document.getElementById('warnaText').value = warna;
        
        // Handle deskripsi
        const deskripsi = eventData.extendedProps?.description || '';
        document.getElementById('deskripsi').value = deskripsi;
        
        console.log('Form filled with:', {
            id: eventData.id,
            nama: eventData.title,
            tanggal: tanggal,
            jenis: jenis,
            warna: warna,
            deskripsi: deskripsi
        });
    }

    function setDefaultColor() {
        const jenisSelect = document.getElementById('jenis');
        const warnaInput = document.getElementById('warna');
        const warnaText = document.getElementById('warnaText');
        
        jenisSelect.addEventListener('change', function() {
            let defaultColor = '#ff0000';
            switch(this.value) {
                case 'nasional':
                    defaultColor = '#ff0000';
                    break;
                case 'keagamaan':
                    defaultColor = '#00ff00';
                    break;
                case 'custom':
                    defaultColor = '#0000ff';
                    break;
            }
            warnaInput.value = defaultColor;
            warnaText.value = defaultColor;
        });
    }

    // Event listeners
    document.getElementById('btnTambahLibur').addEventListener('click', () => openModal('add'));
    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('btnBatal').addEventListener('click', closeModal);
    
    // Color input sync
    document.getElementById('warna').addEventListener('change', function() {
        document.getElementById('warnaText').value = this.value;
    });
    
    document.getElementById('warnaText').addEventListener('input', function() {
        if (/^#[0-9A-F]{6}$/i.test(this.value)) {
            document.getElementById('warna').value = this.value;
        }
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Prevent double submission
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn.disabled) return;
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Menyimpan...';
        
        const formData = new FormData(form);
        const id = document.getElementById('hariLiburId').value;
        const isEdit = id !== '';
        
        const url = isEdit ? 
            `{{ url('admin/hari-libur') }}/${id}` :
            '{{ route("admin.hari-libur.store") }}';
        
        const method = isEdit ? 'PUT' : 'POST';
        
        // Convert FormData to JSON
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });
        
        if (isEdit) {
            data._method = 'PUT';
        }
        
        // Add CSRF token
        data._token = '{{ csrf_token() }}';
        
        // Debug logging
        console.log('Form submission:', {
            isEdit: isEdit,
            id: id,
            url: url,
            method: method,
            data: data
        });
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.textContent = isEdit ? 'Update' : 'Simpan';
            
            if (data.success) {
                Swal.fire('Berhasil!', data.message, 'success');
                closeModal();
                calendar.refetchEvents();
            } else {
                Swal.fire('Error!', data.message, 'error');
            }
        })
        .catch(error => {
            // Re-enable button on error
            submitBtn.disabled = false;
            submitBtn.textContent = isEdit ? 'Update' : 'Simpan';
            
            console.error('Error:', error);
            Swal.fire('Error!', 'Terjadi kesalahan saat menyimpan data', 'error');
        });
    });

    // Show event details with edit/delete options
    function showEventDetails(event) {
        console.log('Event data received in showEventDetails:', event);
        
        Swal.fire({
            title: event.title,
            html: `
                <div class="text-left space-y-2">
                    <p><strong>Tanggal:</strong> ${new Date(event.start).toLocaleDateString('id-ID')}</p>
                    <p><strong>Jenis:</strong> ${getJenisDisplay(event.extendedProps?.jenis || 'nasional')}</p>
                    ${event.extendedProps?.description ? `<p><strong>Deskripsi:</strong> ${event.extendedProps.description}</p>` : ''}
                    <p><strong>Warna:</strong> <span style="color: ${event.color || event.backgroundColor || '#000'}">${event.color || event.backgroundColor || 'tidak diset'}</span></p>
                </div>
            `,
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Edit',
            denyButtonText: 'Hapus',
            cancelButtonText: 'Tutup',
            confirmButtonColor: '#3b82f6',
            denyButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280'
        }).then((result) => {
            if (result.isConfirmed) {
                // Edit event - pass complete event data
                console.log('Opening edit modal with event:', event);
                openModal('edit', null, event);
            } else if (result.isDenied) {
                // Delete event
                deleteEvent(event.id);
            }
        });
    }
    
    function deleteEvent(eventId) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Hari libur akan dihapus secara permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/hari-libur/${eventId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.log('Error response:', text);
                            throw new Error(`HTTP ${response.status}: Server error`);
                        });
                    }
                    
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            console.log('Non-JSON response:', text.substring(0, 200));
                            throw new Error('Server mengembalikan response yang tidak valid');
                        });
                    }
                    
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire('Berhasil!', data.message, 'success');
                        calendar.refetchEvents();
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    Swal.fire('Error!', error.message || 'Terjadi kesalahan saat menghapus data', 'error');
                });
            }
        });
    }
    
    function getJenisDisplay(jenis) {
        switch(jenis) {
            case 'nasional': return 'Hari Libur Nasional';
            case 'keagamaan': return 'Hari Libur Keagamaan';
            case 'custom': return 'Hari Libur Custom';
            default: return 'Unknown';
        }
    }

    // Import holidays button
    document.getElementById('btnImportLibur').addEventListener('click', function() {
        openImportModal();
    });

    // Sync API button
    document.getElementById('btnSyncAPI').addEventListener('click', function() {
        openSyncModal();
    });

    // Sync API Modal Functions
    const syncModal = document.getElementById('modalSyncAPI');
    const formSyncAPI = document.getElementById('formSyncAPI');
    const syncYearSelect = document.getElementById('sync_year');

    // Populate year dropdown
    function populateYears() {
        const currentYear = new Date().getFullYear();
        for (let year = currentYear - 2; year <= currentYear + 2; year++) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            if (year === currentYear) {
                option.selected = true;
            }
            syncYearSelect.appendChild(option);
        }
    }

    function openSyncModal() {
        syncModal.classList.remove('hidden');
        syncModal.classList.add('flex');
        if (syncYearSelect.options.length === 1) {
            populateYears();
        }
    }

    function closeSyncModal() {
        syncModal.classList.add('hidden');
        syncModal.classList.remove('flex');
    }

    // Close sync modal handlers
    document.getElementById('closeSyncModal').addEventListener('click', closeSyncModal);
    document.getElementById('btnCancelSync').addEventListener('click', closeSyncModal);
    
    syncModal.addEventListener('click', function(e) {
        if (e.target === syncModal) {
            closeSyncModal();
        }
    });

    // Sync API Form Handler
    formSyncAPI.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const year = formData.get('year');
        const month = formData.get('month');
        
        let confirmText = `Sinkronisasi data hari libur tahun ${year}`;
        if (month) {
            const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            confirmText += ` bulan ${monthNames[parseInt(month)]}`;
        }
        confirmText += ' dari API?';
        
        Swal.fire({
            title: 'Konfirmasi Sinkronisasi',
            text: confirmText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#9333ea',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Sinkronisasi!',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            allowOutsideClick: () => !Swal.isLoading(),
            preConfirm: () => {
                return fetch('{{ route("admin.hari-libur.sync-api") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        year: year,
                        month: month || null
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || `HTTP ${response.status}: Server error`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message);
                    }
                    return data;
                })
                .catch(error => {
                    Swal.showValidationMessage(`Gagal: ${error.message}`);
                });
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const data = result.value;
                closeSyncModal();
                formSyncAPI.reset();
                calendar.refetchEvents();
                
                let message = `Berhasil sinkronisasi hari libur!<br><br>`;
                message += `<div class="text-left text-sm">`;
                message += `<strong>Ditambahkan:</strong> ${data.data.imported} hari libur<br>`;
                message += `<strong>Diperbarui:</strong> ${data.data.updated} hari libur<br>`;
                message += `<strong>Dilewati:</strong> ${data.data.skipped} hari libur<br>`;
                message += `<strong>Total dari API:</strong> ${data.data.total_holidays} hari libur`;
                
                if (data.data.errors && data.data.errors.length > 0) {
                    message += `<br><br><strong>Error:</strong><br>`;
                    message += data.data.errors.slice(0, 5).join('<br>');
                    if (data.data.errors.length > 5) {
                        message += `<br>... dan ${data.data.errors.length - 5} error lainnya`;
                    }
                }
                message += `</div>`;
                
                Swal.fire({
                    title: 'Sinkronisasi Berhasil!',
                    html: message,
                    icon: 'success',
                    confirmButtonColor: '#9333ea'
                });
            }
        });
    });

    // Import Modal Functions
    const importModal = document.getElementById('modalImport');
    const formImportFile = document.getElementById('formImportFile');

    function openImportModal() {
        importModal.classList.remove('hidden');
        importModal.classList.add('flex');
    }

    function closeImportModal() {
        importModal.classList.add('hidden');
        importModal.classList.remove('flex');
    }

    // Close import modal handlers
    document.getElementById('closeImportModal').addEventListener('click', closeImportModal);
    
    importModal.addEventListener('click', function(e) {
        if (e.target === importModal) {
            closeImportModal();
        }
    });

    // Import File Form Handler
    formImportFile.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const fileName = formData.get('import_file').name;
        
        Swal.fire({
            title: 'Konfirmasi Import',
            text: `Import data dari file ${fileName}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Import',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return fetch('{{ route("admin.hari-libur.import-file") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.log('Error response:', text);
                            throw new Error(`HTTP ${response.status}: Server error`);
                        });
                    }
                    
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            console.log('Non-JSON response:', text.substring(0, 200));
                            throw new Error('Server mengembalikan response yang tidak valid');
                        });
                    }
                    
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message);
                    }
                    return data;
                })
                .catch(error => {
                    console.error('Import error:', error);
                    Swal.showValidationMessage(`Error: ${error.message}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                const data = result.value;
                let errorHtml = '';
                if (data.data.errors && data.data.errors.length > 0) {
                    errorHtml = `
                        <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded">
                            <p class="font-medium text-yellow-800 mb-2">Peringatan:</p>
                            <ul class="text-sm text-yellow-700 list-disc list-inside max-h-32 overflow-y-auto">
                                ${data.data.errors.map(error => `<li>${error}</li>`).join('')}
                            </ul>
                        </div>
                    `;
                }
                
                Swal.fire({
                    title: 'Import Selesai!',
                    html: `
                        <div class="text-left">
                            <p><strong>Berhasil:</strong> ${data.data.imported} data</p>
                            <p><strong>Dilewati:</strong> ${data.data.skipped} data</p>
                            ${errorHtml}
                        </div>
                    `,
                    icon: data.data.errors && data.data.errors.length > 0 ? 'warning' : 'success',
                    width: '500px'
                });
                calendar.refetchEvents();
                closeImportModal();
                formImportFile.reset();
            }
        });
    });

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Initialize default color handler
    setDefaultColor();
});
</script>
@endpush