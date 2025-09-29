@extends('layouts.pembimbing')

@section('title', 'Detail Presensi')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 300px;
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

    .photo-hover {
        transition: transform 0.2s ease-in-out;
    }
    
    .photo-hover:hover {
        transform: scale(1.02);
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <a href="{{ route('pembimbing.kehadiran.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-3">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Daftar Kehadiran
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Detail Presensi</h1>
                <p class="text-sm text-gray-600">
                    {{ \Carbon\Carbon::parse($presensi->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                </p>
            </div>
            
            @php
                $statusConfig = [
                    'hadir' => ['class' => 'bg-green-100 text-green-800', 'icon' => 'fa-check'],
                    'terlambat' => ['class' => 'bg-orange-100 text-orange-800', 'icon' => 'fa-clock'],
                    'izin' => ['class' => 'bg-yellow-100 text-yellow-800', 'icon' => 'fa-calendar-times'],
                    'sakit' => ['class' => 'bg-blue-100 text-blue-800', 'icon' => 'fa-thermometer'],
                    'alpa' => ['class' => 'bg-red-100 text-red-800', 'icon' => 'fa-times']
                ];
                $config = $statusConfig[$presensi->status] ?? $statusConfig['alpa'];
            @endphp
            
            <span class="px-4 py-2 {{ $config['class'] }} rounded-full font-medium text-lg">
                <i class="fas {{ $config['icon'] }} mr-2"></i>
                {{ ucfirst($presensi->status) }}
            </span>
        </div>

        <!-- Informasi Peserta -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full text-white flex items-center justify-center font-semibold text-xl">
                    {{ strtoupper(substr($presensi->peserta->nama_lengkap, 0, 2)) }}
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-gray-900">{{ $presensi->peserta->nama_lengkap }}</h3>
                    <p class="text-sm text-gray-600">NIM: {{ $presensi->peserta->nim }}</p>
                    <p class="text-sm text-gray-600">Email: {{ $presensi->peserta->user->email ?? '-' }}</p>
                    <p class="text-sm text-gray-600">No. HP: {{ $presensi->peserta->no_telepon ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Waktu -->
    @if($presensi->jam_masuk || $presensi->jam_keluar)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Jam Masuk -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-sign-in-alt text-green-600 text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">Check In</h3>
                        <p class="text-2xl font-bold text-green-600">
                            {{ $presensi->jam_masuk ? \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i:s') : '-' }}
                        </p>
                        @if($presensi->jam_masuk && $presensi->jamKerja)
                            <p class="text-sm text-gray-500">
                                Target: {{ \Carbon\Carbon::parse($presensi->jamKerja->jam_masuk)->format('H:i') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Jam Keluar -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-sign-out-alt text-blue-600 text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">Check Out</h3>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ $presensi->jam_keluar ? \Carbon\Carbon::parse($presensi->jam_keluar)->format('H:i:s') : 'Belum checkout' }}
                        </p>
                        @if($presensi->jam_keluar && $presensi->jamKerja)
                            <p class="text-sm text-gray-500">
                                Target: {{ \Carbon\Carbon::parse($presensi->jamKerja->jam_keluar)->format('H:i') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Informasi Durasi dan Status -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Durasi Kerja -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-center">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-stopwatch text-purple-600 text-lg"></i>
                </div>
                <h3 class="text-sm font-medium text-gray-600 mb-1">Durasi Kerja</h3>
                @php
                    $durasi_kerja = '-';
                    if ($presensi->jam_masuk && $presensi->jam_keluar) {
                        $jamMasuk = \Carbon\Carbon::parse($presensi->jam_masuk);
                        $jamKeluar = \Carbon\Carbon::parse($presensi->jam_keluar);
                        $durasi = $jamKeluar->diff($jamMasuk);
                        $durasi_kerja = $durasi->h . ' jam ' . $durasi->i . ' menit';
                    }
                @endphp
                <p class="text-lg font-bold text-purple-600">{{ $durasi_kerja }}</p>
            </div>
        </div>

        <!-- Keterlambatan -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-center">
                <div class="w-12 h-12 bg-{{ $presensi->keterlambatan > 0 ? 'yellow' : 'green' }}-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-{{ $presensi->keterlambatan > 0 ? 'exclamation-triangle' : 'check' }} text-{{ $presensi->keterlambatan > 0 ? 'yellow' : 'green' }}-600 text-lg"></i>
                </div>
                <h3 class="text-sm font-medium text-gray-600 mb-1">Keterlambatan</h3>
                @php
                    $keterlambatan = 'Tepat waktu';
                    if ($presensi->keterlambatan && $presensi->keterlambatan > 0) {
                        $hours = floor($presensi->keterlambatan / 60);
                        $minutes = $presensi->keterlambatan % 60;
                        
                        if ($hours > 0) {
                            $keterlambatan = $hours . ' jam ' . $minutes . ' menit';
                        } else {
                            $keterlambatan = $minutes . ' menit';
                        }
                    }
                @endphp
                <p class="text-lg font-bold text-{{ $presensi->keterlambatan > 0 ? 'yellow' : 'green' }}-600">{{ $keterlambatan }}</p>
            </div>
        </div>

        <!-- Lokasi -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-center">
                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-map-marker-alt text-indigo-600 text-lg"></i>
                </div>
                <h3 class="text-sm font-medium text-gray-600 mb-1">Lokasi</h3>
                <p class="text-lg font-bold text-indigo-600">{{ $presensi->lokasi->nama_lokasi ?? 'Tidak ada data' }}</p>
                @if($presensi->lokasi)
                    <p class="text-sm text-gray-500 mt-1">{{ $presensi->lokasi->alamat }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Foto Presensi -->
    @if($presensi->foto_masuk || $presensi->foto_keluar)
        <div id="foto" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Foto Presensi</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($presensi->foto_masuk)
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-3">Foto Check In</h4>
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                            <img src="{{ asset('storage/' . $presensi->foto_masuk) }}" 
                                 alt="Foto Check In" 
                                 class="w-full h-full object-cover cursor-pointer photo-hover"
                                 onclick="openImageModal(this.src, 'Foto Check In - {{ $presensi->peserta->nama_lengkap }}')">
                        </div>
                    </div>
                @else
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-3">Foto Check In</h4>
                        <div class="aspect-square bg-gray-100 rounded-lg flex items-center justify-center">
                            <div class="text-center">
                                <i class="fas fa-image text-gray-400 text-3xl mb-2"></i>
                                <p class="text-gray-500 text-sm">Tidak ada foto check in</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($presensi->foto_keluar)
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-3">Foto Check Out</h4>
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                            <img src="{{ asset('storage/' . $presensi->foto_keluar) }}" 
                                 alt="Foto Check Out" 
                                 class="w-full h-full object-cover cursor-pointer photo-hover"
                                 onclick="openImageModal(this.src, 'Foto Check Out - {{ $presensi->peserta->nama_lengkap }}')">
                        </div>
                    </div>
                @else
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-3">Foto Check Out</h4>
                        <div class="aspect-square bg-gray-100 rounded-lg flex items-center justify-center">
                            <div class="text-center">
                                <i class="fas fa-image text-gray-400 text-3xl mb-2"></i>
                                <p class="text-gray-500 text-sm">Tidak ada foto check out</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Informasi Lokasi & Peta -->
    @if($presensi->latitude_masuk || $presensi->latitude_keluar)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Lokasi Presensi</h3>
            
            <!-- Map -->
            <div id="map" class="mb-4"></div>
            
            <!-- Koordinat -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($presensi->latitude_masuk)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                            <h4 class="font-medium text-gray-900">Lokasi Check In</h4>
                        </div>
                        <p class="text-gray-600 text-sm">Koordinat: {{ $presensi->latitude_masuk }}, {{ $presensi->longitude_masuk }}</p>
                        @if($presensi->jam_masuk)
                            <p class="text-gray-600 text-sm">Waktu: {{ \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i:s') }}</p>
                        @endif
                    </div>
                @endif

                @if($presensi->latitude_keluar)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-4 h-4 bg-blue-500 rounded-full"></div>
                            <h4 class="font-medium text-gray-900">Lokasi Check Out</h4>
                        </div>
                        <p class="text-gray-600 text-sm">Koordinat: {{ $presensi->latitude_keluar }}, {{ $presensi->longitude_keluar }}</p>
                        @if($presensi->jam_keluar)
                            <p class="text-gray-600 text-sm">Waktu: {{ \Carbon\Carbon::parse($presensi->jam_keluar)->format('H:i:s') }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Catatan / Keterangan -->
    @if($presensi->catatan || $presensi->keterangan_masuk || $presensi->keterangan_keluar)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Catatan & Keterangan</h3>
            
            <div class="space-y-4">
                @if($presensi->catatan)
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Catatan Umum</h4>
                        <p class="text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $presensi->catatan }}</p>
                    </div>
                @endif

                @if($presensi->keterangan_masuk)
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Keterangan Check In</h4>
                        <p class="text-gray-700 bg-green-50 p-3 rounded-lg">{{ $presensi->keterangan_masuk }}</p>
                    </div>
                @endif

                @if($presensi->keterangan_keluar)
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Keterangan Check Out</h4>
                        <p class="text-gray-700 bg-blue-50 p-3 rounded-lg">{{ $presensi->keterangan_keluar }}</p>
                    </div>
                @endif

                @if(!$presensi->catatan && !$presensi->keterangan_masuk && !$presensi->keterangan_keluar)
                    <div class="text-center py-4">
                        <i class="fas fa-sticky-note text-gray-400 text-3xl mb-2"></i>
                        <p class="text-gray-500">Tidak ada catatan khusus untuk presensi ini</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 items-center justify-center p-4 hidden" style="z-index: 9999;">
    <div class="max-w-4xl max-h-full relative">
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75 transition-colors">
            <i class="fas fa-times"></i>
        </button>
        <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain">
        <p id="modalTitle" class="text-white text-center mt-4 text-lg"></p>
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
                    html: '<div style="background: #dc2626; color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="fas fa-building"></i></div>',
                    iconSize: [35, 35],
                    iconAnchor: [17.5, 17.5]
                });
                
                L.marker([{{ $presensi->lokasi->latitude ?? 0 }}, {{ $presensi->lokasi->longitude ?? 0 }}], { icon: officeIcon })
                    .addTo(map)
                    .bindPopup('<strong>{{ $presensi->lokasi->nama_lokasi }}</strong><br>{{ $presensi->lokasi->alamat }}');
                    
                // Add radius circle if available
                @if($presensi->lokasi->radius ?? false)
                    L.circle([{{ $presensi->lokasi->latitude }}, {{ $presensi->lokasi->longitude }}], {
                        color: '#dc2626',
                        fillColor: '#dc2626',
                        fillOpacity: 0.2,
                        radius: {{ $presensi->lokasi->radius }},
                        weight: 2,
                        dashArray: '5, 5'
                    }).addTo(map);
                @endif
            @endif
            
            // Add check-in location
            @if($presensi->latitude_masuk)
                const checkinIcon = L.divIcon({
                    className: 'checkin-marker',
                    html: '<div style="background: #10b981; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="fas fa-sign-in-alt"></i></div>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });
                
                L.marker([{{ $presensi->latitude_masuk }}, {{ $presensi->longitude_masuk }}], { icon: checkinIcon })
                    .addTo(map)
                    .bindPopup('<strong><i class="fas fa-sign-in-alt text-green-600"></i> Check In</strong><br>{{ $presensi->jam_masuk ? \Carbon\Carbon::parse($presensi->jam_masuk)->format("H:i:s") : "Waktu tidak tersedia" }}<br><small>{{ $presensi->latitude_masuk }}, {{ $presensi->longitude_masuk }}</small>');
            @endif
            
            // Add check-out location
            @if($presensi->latitude_keluar)
                const checkoutIcon = L.divIcon({
                    className: 'checkout-marker',
                    html: '<div style="background: #3b82f6; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="fas fa-sign-out-alt"></i></div>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });
                
                L.marker([{{ $presensi->latitude_keluar }}, {{ $presensi->longitude_keluar }}], { icon: checkoutIcon })
                    .addTo(map)
                    .bindPopup('<strong><i class="fas fa-sign-out-alt text-blue-600"></i> Check Out</strong><br>{{ $presensi->jam_keluar ? \Carbon\Carbon::parse($presensi->jam_keluar)->format("H:i:s") : "Waktu tidak tersedia" }}<br><small>{{ $presensi->latitude_keluar }}, {{ $presensi->longitude_keluar }}</small>');
            @endif
            
            // Fit map to show all markers
            const markers = [];
            @if($presensi->lokasi && isset($presensi->lokasi->latitude))
                markers.push(L.marker([{{ $presensi->lokasi->latitude }}, {{ $presensi->lokasi->longitude }}]));
            @endif
            @if($presensi->latitude_masuk)
                markers.push(L.marker([{{ $presensi->latitude_masuk }}, {{ $presensi->longitude_masuk }}]));
            @endif
            @if($presensi->latitude_keluar)
                markers.push(L.marker([{{ $presensi->latitude_keluar }}, {{ $presensi->longitude_keluar }}]));
            @endif
            
            if (markers.length > 1) {
                const group = new L.featureGroup(markers);
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

    // Handle ESC key for modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });
</script>
@endpush