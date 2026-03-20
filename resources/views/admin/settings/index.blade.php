@extends('layouts.main')

@section('title', 'Pengaturan Sistem - Day-In')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-cog mr-2 text-blue-600"></i>Pengaturan Sistem
                </h1>
                <p class="text-gray-600 mt-1">Kelola pengaturan aplikasi presensi</p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        @foreach($settings as $group => $groupSettings)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                        @switch($group)
                            @case('presensi')
                                <i class="fas fa-clipboard-check text-blue-600 mr-2"></i>
                                Pengaturan Presensi
                                @break
                            @case('system')
                                <i class="fas fa-server text-purple-600 mr-2"></i>
                                Pengaturan Sistem
                                @break
                            @default
                                <i class="fas fa-sliders-h text-gray-600 mr-2"></i>
                                Pengaturan Umum
                        @endswitch
                    </h2>
                </div>
                <div class="p-6">
                    @foreach($groupSettings as $setting)
                        <div class="flex items-start justify-between py-4 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                            <div class="flex-1 pr-4">
                                <label class="text-sm font-semibold text-gray-900 block mb-1">
                                    {{ $setting->label }}
                                </label>
                                @if($setting->description)
                                    <p class="text-sm text-gray-500">{{ $setting->description }}</p>
                                @endif
                            </div>
                            <div class="flex-shrink-0">
                                @if($setting->type === 'boolean')
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               class="sr-only peer setting-toggle" 
                                               name="settings[{{ $setting->key }}]"
                                               value="1"
                                               {{ $setting->value == '1' ? 'checked' : '' }}
                                               data-key="{{ $setting->key }}">
                                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        <span class="ml-3 text-sm font-medium text-gray-900">
                                            <span class="status-text">{{ $setting->value == '1' ? 'Aktif' : 'Nonaktif' }}</span>
                                        </span>
                                    </label>
                                @elseif($setting->type === 'string')
                                    <input type="text" 
                                           class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                           name="settings[{{ $setting->key }}]" 
                                           value="{{ $setting->value }}">
                                @elseif($setting->type === 'integer')
                                    <input type="number" 
                                           class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                           name="settings[{{ $setting->key }}]" 
                                           value="{{ $setting->value }}">
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex justify-end space-x-3">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-md transition-colors duration-200">
                <i class="fas fa-times mr-2"></i>
                Batal
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors duration-200">
                <i class="fas fa-save mr-2"></i>
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Update status text when toggle changes
    document.querySelectorAll('.setting-toggle').forEach(input => {
        input.addEventListener('change', function() {
            const statusText = this.closest('label').querySelector('.status-text');
            if (this.checked) {
                statusText.textContent = 'Aktif';
            } else {
                statusText.textContent = 'Nonaktif';
            }
        });
    });
</script>
@endpush
@endsection
