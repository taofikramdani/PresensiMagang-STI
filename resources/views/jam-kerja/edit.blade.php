@extends('layouts.main')

@section('title', 'Edit Jam Kerja - Presensi Magang')

@section('content')
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <!-- Header -->
    <div class="text-black px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-semibold flex items-center">
                <i class="fas fa-edit mr-2 text-blue-600"></i>
                Edit Jam Kerja
            </h1>
            <a href="{{ route('admin.jam-kerja.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
        <p class="text-gray-600 text-sm mt-1">Edit jadwal jam kerja: {{ $jamKerja->nama_shift }}</p>
    </div>

    <div class="p-6">
        <form action="{{ route('admin.jam-kerja.update', $jamKerja) }}" method="POST" class="max-w-2xl">
            @csrf
            @method('PUT')

            <!-- Nama Shift -->
            <div class="mb-6">
                <label for="nama_shift" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Shift <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="nama_shift" 
                       name="nama_shift" 
                       value="{{ old('nama_shift', $jamKerja->nama_shift) }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('nama_shift') border-red-300 @enderror"
                       placeholder="Contoh: Normal, Siang, Malam"
                       required>
                @error('nama_shift')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Jam Masuk dan Keluar -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="jam_masuk" class="block text-sm font-medium text-gray-700 mb-2">
                        Jam Masuk <span class="text-red-500">*</span>
                    </label>
                    <input type="time" 
                           id="jam_masuk" 
                           name="jam_masuk" 
                           value="{{ old('jam_masuk', date('H:i', strtotime($jamKerja->jam_masuk))) }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('jam_masuk') border-red-300 @enderror"
                           required>
                    @error('jam_masuk')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jam_keluar" class="block text-sm font-medium text-gray-700 mb-2">
                        Jam Keluar <span class="text-red-500">*</span>
                    </label>
                    <input type="time" 
                           id="jam_keluar" 
                           name="jam_keluar" 
                           value="{{ old('jam_keluar', date('H:i', strtotime($jamKerja->jam_keluar))) }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('jam_keluar') border-red-300 @enderror"
                           required>
                    @error('jam_keluar')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="toleransi_keterlambatan" class="block text-sm font-medium text-gray-700 mb-2">
                        Toleransi Keterlambatan <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="toleransi_keterlambatan" 
                           name="toleransi_keterlambatan" 
                           value="{{ old('toleransi_keterlambatan',$jamKerja->toleransi_keterlambatan) }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('jam_keluar') border-red-300 @enderror"
                           required>
                    @error('toleransi_keterlambatan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Hari Kerja -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Hari Kerja <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @php
                        $hari = [
                            'senin' => 'Senin',
                            'selasa' => 'Selasa', 
                            'rabu' => 'Rabu',
                            'kamis' => 'Kamis',
                            'jumat' => 'Jumat',
                            'sabtu' => 'Sabtu',
                            'minggu' => 'Minggu'
                        ];
                        $selectedHari = old('hari_kerja', $jamKerja->hari_kerja ?? []);
                    @endphp
                    
                    @foreach($hari as $key => $value)
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="hari_kerja[]" 
                               value="{{ $key }}"
                               {{ in_array($key, $selectedHari ?? []) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">{{ $value }}</span>
                    </label>
                    @endforeach
                </div>
                @error('hari_kerja')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.jam-kerja.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow-sm">
                    <i class="fas fa-save mr-2"></i>
                    Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
