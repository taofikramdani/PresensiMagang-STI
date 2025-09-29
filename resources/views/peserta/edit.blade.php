@extends('layouts.main')

@section('title', 'Edit Peserta')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Peserta Magang</h1>
                <p class="text-gray-600 mt-1">Edit data peserta: <strong>{{ $peserta->nama_lengkap }}</strong></p>
            </div>
            <a href="{{ route('admin.peserta.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Form Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('admin.peserta.update', $peserta) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Data User -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Data Akun</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                        <input type="text" 
                               name="nama_lengkap" 
                               id="nama_lengkap" 
                               value="{{ old('nama_lengkap', $peserta->nama_lengkap) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                               required>
                        @error('nama_lengkap')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email', $peserta->user->email) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                               required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                        <input type="text" 
                               name="username" 
                               id="username" 
                               value="{{ old('username', $peserta->user->name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('username') border-red-500 @enderror"
                               required>
                        @error('username')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password <span class="text-gray-500">(Kosongkan jika tidak ingin mengubah)</span></label>
                        <input type="password" 
                               name="password" 
                               id="password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Data Peserta -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Data Peserta</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nim" class="block text-sm font-medium text-gray-700 mb-2">NIM/NISN *</label>
                        <input type="text" 
                               name="nim" 
                               id="nim" 
                               value="{{ old('nim', $peserta->nim) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nim') border-red-500 @enderror"
                               required>
                        @error('nim')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jurusan" class="block text-sm font-medium text-gray-700 mb-2">Jurusan *</label>
                        <input type="text" 
                               name="jurusan" 
                               id="jurusan" 
                               value="{{ old('jurusan', $peserta->jurusan) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('jurusan') border-red-500 @enderror"
                               required>
                        @error('jurusan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="universitas" class="block text-sm font-medium text-gray-700 mb-2">Universitas/Sekolah *</label>
                        <input type="text" 
                               name="universitas" 
                               id="universitas" 
                               value="{{ old('universitas', $peserta->universitas) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('universitas') border-red-500 @enderror"
                               required>
                        @error('universitas')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-2">No. HP *</label>
                        <input type="text" 
                               name="no_hp" 
                               id="no_hp" 
                               value="{{ old('no_hp', $peserta->no_telepon) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('no_hp') border-red-500 @enderror"
                               required>
                        @error('no_hp')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="pembimbing_id" class="block text-sm font-medium text-gray-700 mb-2">Pembimbing *</label>
                        <select name="pembimbing_id" 
                                id="pembimbing_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('pembimbing_id') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Pembimbing</option>
                            @foreach($pembimbing as $p)
                                <option value="{{ $p->user_id }}" 
                                        {{ old('pembimbing_id', $peserta->pembimbing_id) == $p->user_id ? 'selected' : '' }}>
                                    {{ $p->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                        @error('pembimbing_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="lokasi_id" class="block text-sm font-medium text-gray-700 mb-2">Lokasi Magang *</label>
                        <select name="lokasi_id" 
                                id="lokasi_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('lokasi_id') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Lokasi</option>
                            @foreach($lokasi as $l)
                                <option value="{{ $l->id }}" 
                                        {{ old('lokasi_id', $peserta->lokasi_id) == $l->id ? 'selected' : '' }}>
                                    {{ $l->nama_lokasi }}
                                </option>
                            @endforeach
                        </select>
                        @error('lokasi_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">Alamat *</label>
                        <textarea name="alamat" 
                                  id="alamat" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('alamat') border-red-500 @enderror"
                                  required>{{ old('alamat', $peserta->alamat) }}</textarea>
                        @error('alamat')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Periode Magang dan Status -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Periode Magang & Status</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai *</label>
                        <input type="date" 
                               name="tanggal_mulai" 
                               id="tanggal_mulai" 
                               value="{{ old('tanggal_mulai', $peserta->tanggal_mulai ? $peserta->tanggal_mulai->format('Y-m-d') : '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tanggal_mulai') border-red-500 @enderror"
                               required>
                        @error('tanggal_mulai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai *</label>
                        <input type="date" 
                               name="tanggal_selesai" 
                               id="tanggal_selesai" 
                               value="{{ old('tanggal_selesai', $peserta->tanggal_selesai ? $peserta->tanggal_selesai->format('Y-m-d') : '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tanggal_selesai') border-red-500 @enderror"
                               required>
                        @error('tanggal_selesai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                        <select name="status" 
                                id="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror"
                                required>
                            <option value="aktif" {{ old('status', $peserta->status) == 'aktif' ? 'selected' : '' }}>
                                Aktif
                            </option>
                            <option value="non-aktif" {{ old('status', $peserta->status) == 'non-aktif' ? 'selected' : '' }}>
                                Non-Aktif
                            </option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-4 pt-6">
                <a href="{{ route('admin.peserta.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-save mr-2"></i>
                    Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tanggalMulai = document.getElementById('tanggal_mulai');
    const tanggalSelesai = document.getElementById('tanggal_selesai');
    const statusSelect = document.getElementById('status');
    const originalTanggalSelesai = '{{ $peserta->tanggal_selesai ? $peserta->tanggal_selesai->format("Y-m-d") : "" }}';
    const originalStatus = '{{ $peserta->status }}';
    
    // Update minimum date for tanggal_selesai when tanggal_mulai changes
    tanggalMulai.addEventListener('change', function() {
        tanggalSelesai.setAttribute('min', this.value);
        
        // Clear tanggal_selesai if it's before the new tanggal_mulai
        if (tanggalSelesai.value && tanggalSelesai.value < this.value) {
            tanggalSelesai.value = '';
        }
        
        checkAutoStatusChange();
    });

    // Check for auto status change when tanggal_selesai changes
    tanggalSelesai.addEventListener('change', function() {
        checkAutoStatusChange();
    });
    
    // Function to check if status should be automatically changed
    function checkAutoStatusChange() {
        const newTanggalSelesai = tanggalSelesai.value;
        const currentStatus = statusSelect.value;
        const today = new Date().toISOString().split('T')[0];
        
        // Remove any existing warning
        const existingWarning = document.getElementById('status-warning');
        if (existingWarning) {
            existingWarning.remove();
        }
        
        // Check if tanggal selesai is extended and status is non-aktif
        if (originalTanggalSelesai && newTanggalSelesai && 
            newTanggalSelesai > originalTanggalSelesai && 
            newTanggalSelesai >= today && 
            originalStatus === 'non-aktif') {
            
            // Show warning that status will be automatically changed to aktif
            const warningDiv = document.createElement('div');
            warningDiv.id = 'status-warning';
            warningDiv.className = 'mt-2 p-3 bg-green-50 border border-green-200 rounded-md';
            warningDiv.innerHTML = `
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-800">
                            <strong>Info:</strong> Karena periode magang diperpanjang, status akan otomatis berubah menjadi <strong>AKTIF</strong>
                        </p>
                    </div>
                </div>
            `;
            
            // Insert after status select
            statusSelect.parentNode.appendChild(warningDiv);
        }
    }

    // Set initial minimum date for tanggal_selesai
    if (tanggalMulai.value) {
        tanggalSelesai.setAttribute('min', tanggalMulai.value);
    }
    
    // Check on initial load
    checkAutoStatusChange();
});
</script>
@endsection
