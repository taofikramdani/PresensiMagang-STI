@extends('layouts.peserta')

@section('title', 'Detail Presensi | Presensi STI')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 250px;
        border-radius: 8px;
        z-index: 1;
        position: relative;
    }
    
    .leaflet-container {
        z-index: 1 !important;
    }
    
    .leaflet-control-container {
        z-index: 2 !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-4 p-4">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <a href="{{ route('peserta.riwayat.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-2">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
                <h1 class="text-lg font-bold text-gray-900">Detail Presensi</h1>
                <p class="text-sm text-gray-600">
                    {{ \Carbon\Carbon::parse($presensi->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                </p>
            </div>
            
            @php
                $statusConfig = [
                    'hadir' => ['class' => 'bg-green-100 text-green-800', 'icon' => 'fa-check'],
                    'terlambat' => ['class' => 'bg-yellow-100 text-yellow-800', 'icon' => 'fa-clock'],
                    'izin' => ['class' => 'bg-blue-100 text-blue-800', 'icon' => 'fa-file-medical'],
                    'alpha' => ['class' => 'bg-red-100 text-red-800', 'icon' => 'fa-times'],
                    'sakit' => ['class' => 'bg-purple-100 text-purple-800', 'icon' => 'fa-thermometer-half']
                ];
                $config = $statusConfig[$presensi->status] ?? $statusConfig['alpha'];
            @endphp
            
            <span class="px-3 py-1 {{ $config['class'] }} rounded-full font-medium text-sm">
                <i class="fas {{ $config['icon'] }} mr-1"></i>
                {{ $presensi->getStatusLabel() }}
            </span>
        </div>
    </div>

    <!-- Informasi Waktu -->
    @if($presensi->jam_masuk || $presensi->jam_keluar)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <!-- Jam Masuk -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-sign-in-alt text-green-600 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-gray-900">Check In</h3>
                        <p class="text-lg font-bold text-green-600">
                            {{ $presensi->jam_masuk ? \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i:s') : '-' }}
                        </p>
                        @if($presensi->jam_masuk && $presensi->jamKerja)
                            <p class="text-xs text-gray-500">
                                Target: {{ \Carbon\Carbon::parse($presensi->jamKerja->jam_masuk)->format('H:i') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Jam Keluar -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-sign-out-alt text-blue-600 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-gray-900">Check Out</h3>
                        <p class="text-lg font-bold text-blue-600">
                            {{ $presensi->jam_keluar ? \Carbon\Carbon::parse($presensi->jam_keluar)->format('H:i:s') : 'Belum checkout' }}
                        </p>
                        @if($presensi->jam_keluar && $presensi->jamKerja)
                            <p class="text-xs text-gray-500">
                                Target: {{ \Carbon\Carbon::parse($presensi->jamKerja->jam_keluar)->format('H:i') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Informasi Durasi dan Status -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @if($presensi->durasi_kerja)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
                <div class="text-center">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-stopwatch text-purple-600 text-sm"></i>
                    </div>
                    <h3 class="text-xs font-medium text-gray-600">Durasi Kerja</h3>
                    <p class="text-sm font-bold text-purple-600">{{ $presensi->getDurasiKerjaFormatted() }}</p>
                </div>
            </div>
        @endif

        @if($presensi->keterlambatan > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
                <div class="text-center">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-sm"></i>
                    </div>
                    <h3 class="text-xs font-medium text-gray-600">Keterlambatan</h3>
                    <p class="text-sm font-bold text-yellow-600">{{ $presensi->getKeterlambatanFormatted() }}</p>
                </div>
            </div>
        @endif

        @if($presensi->jamKerja)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
                <div class="text-center">
                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-business-time text-indigo-600 text-sm"></i>
                    </div>
                    <h3 class="text-xs font-medium text-gray-600">Shift</h3>
                    <p class="text-sm font-bold text-indigo-600">{{ $presensi->jamKerja->nama_shift }}</p>
                    <p class="text-xs text-gray-500">
                        {{ \Carbon\Carbon::parse($presensi->jamKerja->jam_masuk)->format('H:i') }} - 
                        {{ \Carbon\Carbon::parse($presensi->jamKerja->jam_keluar)->format('H:i') }}
                    </p>
                </div>
            </div>
        @endif
    </div>

    <!-- Foto Presensi -->
    @if($presensi->foto_masuk || $presensi->foto_keluar)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Foto Presensi</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($presensi->foto_masuk)
                    <div>
                        <h4 class="text-xs font-medium text-gray-600 mb-2">Foto Check In</h4>
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                            <img src="{{ $presensi->getFotoMasukUrl() }}" 
                                 alt="Foto Check In" 
                                 class="w-full h-full object-cover cursor-pointer"
                                 onclick="openImageModal(this.src, 'Foto Check In')">
                        </div>
                    </div>
                @endif

                @if($presensi->foto_keluar)
                    <div>
                        <h4 class="text-xs font-medium text-gray-600 mb-2">Foto Check Out</h4>
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                            <img src="{{ $presensi->getFotoKeluarUrl() }}" 
                                 alt="Foto Check Out" 
                                 class="w-full h-full object-cover cursor-pointer"
                                 onclick="openImageModal(this.src, 'Foto Check Out')">
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Informasi Lokasi -->
    @if($presensi->latitude_masuk || $presensi->latitude_keluar)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Lokasi Presensi</h3>
            
            <!-- Map -->
            <div id="map" class="mb-3"></div>
            
            <!-- Koordinat -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                @if($presensi->latitude_masuk)
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-1">Lokasi Check In</h4>
                        <p class="text-gray-600">{{ $presensi->latitude_masuk }}, {{ $presensi->longitude_masuk }}</p>
                    </div>
                @endif

                @if($presensi->latitude_keluar)
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-1">Lokasi Check Out</h4>
                        <p class="text-gray-600">{{ $presensi->latitude_keluar }}, {{ $presensi->longitude_keluar }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Keterangan -->
    @if($presensi->keterangan)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Keterangan</h3>
            <p class="text-gray-700 bg-gray-50 p-3 rounded-lg text-sm">{{ $presensi->keterangan }}</p>
        </div>
    @endif
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 items-center justify-center p-4 hidden" style="z-index: 9999;">
    <div class="max-w-4xl max-h-full relative">
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full w-8 h-8 flex items-center justify-center hover:bg-opacity-75 transition-colors">
            <i class="fas fa-times"></i>
        </button>
        <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain">
        <p id="modalTitle" class="text-white text-center mt-4"></p>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let map;
    
    // Initialize map if location data exists
    @if($presensi->latitude_masuk || $presensi->latitude_keluar)
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
        });
        
        function initMap() {
            // Set initial view based on first available location
            @if($presensi->latitude_masuk)
                const initialLat = {{ $presensi->latitude_masuk }};
                const initialLng = {{ $presensi->longitude_masuk }};
            @else
                const initialLat = {{ $presensi->latitude_keluar }};
                const initialLng = {{ $presensi->longitude_keluar }};
            @endif
            
            map = L.map('map').setView([initialLat, initialLng], 16);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            // Add office location if available
            @if($presensi->lokasi)
                const officeIcon = L.divIcon({
                    className: 'office-marker',
                    html: '<div style="background: #dc2626; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="fas fa-building"></i></div>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });
                
                L.marker([{{ $presensi->lokasi->latitude }}, {{ $presensi->lokasi->longitude }}], { icon: officeIcon })
                    .addTo(map)
                    .bindPopup('{{ $presensi->lokasi->nama_lokasi }}');
                    
                // Add radius circle
                L.circle([{{ $presensi->lokasi->latitude }}, {{ $presensi->lokasi->longitude }}], {
                    color: '#dc2626',
                    fillColor: '#dc2626',
                    fillOpacity: 0.2,
                    radius: {{ $presensi->lokasi->radius }},
                    weight: 2,
                    dashArray: '5, 5'
                }).addTo(map);
            @endif
            
            // Add check-in location
            @if($presensi->latitude_masuk)
                const checkinIcon = L.divIcon({
                    className: 'checkin-marker',
                    html: '<div style="background: #10b981; color: white; width: 25px; height: 25px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="fas fa-sign-in-alt"></i></div>',
                    iconSize: [25, 25],
                    iconAnchor: [12.5, 12.5]
                });
                
                L.marker([{{ $presensi->latitude_masuk }}, {{ $presensi->longitude_masuk }}], { icon: checkinIcon })
                    .addTo(map)
                    .bindPopup('Check In: {{ $presensi->jam_masuk ? \Carbon\Carbon::parse($presensi->jam_masuk)->format("H:i:s") : "-" }}');
            @endif
            
            // Add check-out location
            @if($presensi->latitude_keluar)
                const checkoutIcon = L.divIcon({
                    className: 'checkout-marker',
                    html: '<div style="background: #3b82f6; color: white; width: 25px; height: 25px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="fas fa-sign-out-alt"></i></div>',
                    iconSize: [25, 25],
                    iconAnchor: [12.5, 12.5]
                });
                
                L.marker([{{ $presensi->latitude_keluar }}, {{ $presensi->longitude_keluar }}], { icon: checkoutIcon })
                    .addTo(map)
                    .bindPopup('Check Out: {{ $presensi->jam_keluar ? \Carbon\Carbon::parse($presensi->jam_keluar)->format("H:i:s") : "-" }}');
            @endif
            
            // Fit map to show all markers
            const markers = [];
            @if($presensi->lokasi)
                markers.push([{{ $presensi->lokasi->latitude }}, {{ $presensi->lokasi->longitude }}]);
            @endif
            @if($presensi->latitude_masuk)
                markers.push([{{ $presensi->latitude_masuk }}, {{ $presensi->longitude_masuk }}]);
            @endif
            @if($presensi->latitude_keluar)
                markers.push([{{ $presensi->latitude_keluar }}, {{ $presensi->longitude_keluar }}]);
            @endif
            
            if (markers.length > 1) {
                const group = new L.featureGroup(markers.map(m => L.marker(m)));
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }
    @endif
    
    // Image modal functions
    function openImageModal(src, title) {
        const modal = document.getElementById('imageModal');
        document.getElementById('modalImage').src = src;
        document.getElementById('modalTitle').textContent = title;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    
    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    // Handle image load errors
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('img[src*="storage"]').forEach(function(img) {
            img.addEventListener('error', function() {
                this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIGZpbGw9Im5vbmUiIHZpZXdCb3g9IjAgMCAyNCAyNCIgc3Ryb2tlPSIjOTk5Ij48cGF0aCBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIHN0cm9rZS13aWR0aD0iMiIgZD0ibTE1IDlzMSAxIDQgMWMzIDAgNCAyIDQgNWMwIDMtMSA0LTQgNEg2bC00LTRzLTEtMS0xLTQgMS00IDQtNGMzIDAgNCAxIDQgMnoiLz48cGF0aCBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIHN0cm9rZS13aWR0aD0iMiIgZD0ibTkgOSAzIDMgMy0zIi8+PHBhdGggc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBzdHJva2Utd2lkdGg9IjIiIGQ9Im0xMiAxMiAwLTMiLz48L3N2Zz4=';
                this.alt = 'Gambar tidak dapat dimuat';
                this.style.filter = 'grayscale(1)';
                this.style.opacity = '0.5';
            });
        });
    });
    
    // Close modal when clicking outside
    document.getElementById('imageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeImageModal();
        }
    });
</script>
@endpush
