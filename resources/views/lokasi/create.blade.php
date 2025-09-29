@extends('layouts.main')

@section('title', 'Tambah Lokasi')

@section('content')
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Tambah Lokasi Baru</h1>
                    <p class="text-gray-600 mt-1">Tambahkan lokasi presensi dengan memilih koordinat di peta</p>
                </div>
                <a href="{{ route('admin.lokasi.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Left Panel - Form -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-edit mr-2 text-blue-600"></i>
                        Detail Lokasi
                    </h2>
                </div>
                    
                    <div class="p-6">
                                            <form action="{{ route('admin.lokasi.store') }}" method="POST" class="space-y-6">
                            @csrf
                            
                            <!-- Nama Lokasi -->
                            <div class="mb-6">
                                <label for="nama_lokasi" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Lokasi <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="nama_lokasi" 
                                       name="nama_lokasi" 
                                       value="{{ old('nama_lokasi') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nama_lokasi') border-red-500 @enderror"
                                       placeholder="Masukkan nama lokasi"
                                       required>
                                @error('nama_lokasi')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Alamat -->
                            <div class="mb-6">
                                <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">
                                    Alamat <span class="text-red-500">*</span>
                                </label>
                                <textarea id="alamat" 
                                          name="alamat" 
                                          rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('alamat') border-red-500 @enderror"
                                          placeholder="Masukkan alamat lengkap"
                                          required>{{ old('alamat') }}</textarea>
                                @error('alamat')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Koordinat -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label for="latitude" class="block text-sm font-medium text-gray-700 mb-2">
                                        Latitude <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="latitude" 
                                           name="latitude" 
                                           value="{{ old('latitude', request('lat')) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm @error('latitude') border-red-500 @enderror"
                                           placeholder="-6.2088"
                                           readonly>
                                    @error('latitude')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="longitude" class="block text-sm font-medium text-gray-700 mb-2">
                                        Longitude <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="longitude" 
                                           name="longitude" 
                                           value="{{ old('longitude', request('lng')) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm @error('longitude') border-red-500 @enderror"
                                           placeholder="106.8456"
                                           readonly>
                                    @error('longitude')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Radius -->
                            <div class="mb-6">
                                <label for="radius" class="block text-sm font-medium text-gray-700 mb-2">
                                    Radius (meter) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" 
                                           id="radius" 
                                           name="radius" 
                                           value="{{ old('radius', 100) }}"
                                           min="1" 
                                           max="1000"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('radius') border-red-500 @enderror"
                                           placeholder="100"
                                           required>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">meter</span>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Radius area presensi (1-1000 meter)</p>
                                @error('radius')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Keterangan -->
                            <div class="mb-6">
                                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Keterangan
                                </label>
                                <textarea id="keterangan" 
                                          name="keterangan" 
                                          rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Keterangan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                            </div>

                            <!-- Instructions -->
                            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                    <div class="text-sm text-blue-700">
                                        <p class="font-medium mb-1">Cara memilih lokasi:</p>
                                        <ul class="text-xs space-y-1">
                                            <li>• Klik pada peta di sebelah kanan untuk menentukan koordinat</li>
                                            <li>• Seret marker untuk menyesuaikan posisi</li>
                                            <li>• Koordinat akan otomatis terisi</li>
                                            <li>• Atur radius sesuai kebutuhan</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex space-x-3">
                                <button type="submit" 
                                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg shadow-md transition duration-200 ease-in-out transform hover:scale-105 flex items-center justify-center space-x-2">
                                    <i class="fas fa-save"></i>
                                    <span>Simpan Lokasi</span>
                                </button>
                                <button type="button" 
                                        id="clearMap"
                                        class="px-4 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition duration-200 flex items-center space-x-2">
                                    <i class="fas fa-eraser"></i>
                                    <span>Clear</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Panel - Interactive Map -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-map mr-2 text-green-600"></i>
                            Pilih Lokasi di Peta
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Klik di peta untuk menentukan koordinat lokasi</p>
                    </div>
                    
                    <!-- Map Container -->
                    <div class="relative">
                        <div id="map" class="h-[500px] min-h-[400px]"></div>
                        
                        <!-- Map Loading Overlay -->
                        <div id="map-loading" class="absolute inset-0 bg-gray-100 flex items-center justify-center">
                            <div class="text-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
                                <p class="text-gray-600">Memuat peta...</p>
                            </div>
                        </div>

                        <!-- Map Controls -->
                        <div class="absolute top-4 right-4 space-y-2">
                            <button id="getCurrentLocation" 
                                    class="bg-white hover:bg-gray-50 p-2 rounded-lg shadow-md border transition duration-200"
                                    title="Dapatkan lokasi saat ini">
                                <i class="fas fa-crosshairs text-gray-600"></i>
                            </button>
                            <button id="searchLocation" 
                                    class="bg-white hover:bg-gray-50 p-2 rounded-lg shadow-md border transition duration-200"
                                    title="Cari lokasi">
                                <i class="fas fa-search text-gray-600"></i>
                            </button>
                        </div>

                        <!-- Coordinates Display -->
                        <div id="coordinates-display" class="absolute bottom-4 left-4 bg-white p-2 rounded-lg shadow-md border">
                            <div class="text-xs text-gray-600">
                                <div>Lat: <span id="display-lat" class="font-mono">-</span></div>
                                <div>Lng: <span id="display-lng" class="font-mono">-</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


<!-- Include Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let map;
    let marker;
    let circle;

    // Initialize map
    document.addEventListener('DOMContentLoaded', function() {
        initializeMap();
        setupEventListeners();
    });

    function initializeMap() {
        // Use provided coordinates or default to Jakarta
        const defaultLat = {{ request('lat', -6.2088) }};
        const defaultLng = {{ request('lng', 106.8456) }};

        map = L.map('map').setView([defaultLat, defaultLng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Hide loading overlay
        const loadingElement = document.getElementById('map-loading');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }

        // Add initial marker if coordinates provided
        if ({{ request('lat') ? 'true' : 'false' }}) {
            addMarker(defaultLat, defaultLng);
        }

        // Add click event to map
        map.on('click', function(e) {
            addMarker(e.latlng.lat, e.latlng.lng);
        });

        // Update coordinates display on mouse move
        map.on('mousemove', function(e) {
            const displayLat = document.getElementById('display-lat');
            const displayLng = document.getElementById('display-lng');
            if (displayLat && displayLng) {
                displayLat.textContent = e.latlng.lat.toFixed(6);
                displayLng.textContent = e.latlng.lng.toFixed(6);
            }
        });
    }

    function addMarker(lat, lng) {
        // Remove existing marker and circle
        if (marker) {
            map.removeLayer(marker);
        }
        if (circle) {
            map.removeLayer(circle);
        }

        // Add new marker
        marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);

        // Add radius circle
        const radiusInput = document.getElementById('radius');
        const radius = parseInt(radiusInput ? radiusInput.value : 100) || 100;
        circle = L.circle([lat, lng], {
            color: 'blue',
            fillColor: '#3b82f6',
            fillOpacity: 0.2,
            radius: radius
        }).addTo(map);

        // Update form inputs
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        if (latInput) latInput.value = lat.toFixed(8);
        if (lngInput) lngInput.value = lng.toFixed(8);

        // Add marker drag event
        marker.on('drag', function(e) {
            const newLat = e.target.getLatLng().lat;
            const newLng = e.target.getLatLng().lng;
            
            // Update circle position
            circle.setLatLng([newLat, newLng]);
            
            // Update form inputs
            if (latInput) latInput.value = newLat.toFixed(8);
            if (lngInput) lngInput.value = newLng.toFixed(8);
        });

        // Bind popup to marker
        marker.bindPopup(`
            <div class="text-sm">
                <h4 class="font-semibold mb-1">Lokasi Terpilih</h4>
                <p class="text-gray-600 mb-1">Lat: ${lat.toFixed(8)}</p>
                <p class="text-gray-600 mb-1">Lng: ${lng.toFixed(8)}</p>
                <p class="text-gray-600">Radius: ${radius}m</p>
            </div>
        `).openPopup();
    }

    function updateCircleRadius() {
        if (circle) {
            const radiusInput = document.getElementById('radius');
            const radius = parseInt(radiusInput ? radiusInput.value : 100) || 100;
            circle.setRadius(radius);
            
            // Update popup
            if (marker) {
                const latlng = marker.getLatLng();
                marker.bindPopup(`
                    <div class="text-sm">
                        <h4 class="font-semibold mb-1">Lokasi Terpilih</h4>
                        <p class="text-gray-600 mb-1">Lat: ${latlng.lat.toFixed(8)}</p>
                        <p class="text-gray-600 mb-1">Lng: ${latlng.lng.toFixed(8)}</p>
                        <p class="text-gray-600">Radius: ${radius}m</p>
                    </div>
                `);
            }
        }
    }

    function setupEventListeners() {
        // Radius input change
        const radiusInput = document.getElementById('radius');
        if (radiusInput) {
            radiusInput.addEventListener('input', updateCircleRadius);
        }

        // Get current location
        const getCurrentLocationBtn = document.getElementById('getCurrentLocation');
        if (getCurrentLocationBtn) {
            getCurrentLocationBtn.addEventListener('click', function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        map.setView([lat, lng], 16);
                        addMarker(lat, lng);
                    }, function(error) {
                        alert('Tidak dapat mengakses lokasi: ' + error.message);
                    });
                } else {
                    alert('Geolocation tidak didukung oleh browser ini.');
                }
            });
        }

        // Search location
        const searchLocationBtn = document.getElementById('searchLocation');
        if (searchLocationBtn) {
            searchLocationBtn.addEventListener('click', function() {
                const query = prompt('Masukkan nama lokasi yang ingin dicari:');
                if (query) {
                    searchLocation(query);
                }
            });
        }

        // Clear map
        const clearMapBtn = document.getElementById('clearMap');
        if (clearMapBtn) {
            clearMapBtn.addEventListener('click', function() {
                if (marker) {
                    map.removeLayer(marker);
                    marker = null;
                }
                if (circle) {
                    map.removeLayer(circle);
                    circle = null;
                }
                
                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');
                if (latInput) latInput.value = '';
                if (lngInput) lngInput.value = '';
            });
        }

        // Form validation
        const form = document.getElementById('lokasiForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');
                const lat = latInput ? latInput.value : '';
                const lng = lngInput ? lngInput.value : '';
                
                if (!lat || !lng) {
                    e.preventDefault();
                    alert('Silakan pilih lokasi di peta terlebih dahulu!');
                    return false;
                }
            });
        }
    }

    function searchLocation(query) {
        // Use Nominatim API for geocoding
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    
                    map.setView([lat, lng], 16);
                    addMarker(lat, lng);
                } else {
                    alert('Lokasi tidak ditemukan. Silakan coba dengan kata kunci lain.');
                }
            })
            .catch(error => {
                console.error('Error searching location:', error);
                alert('Terjadi kesalahan saat mencari lokasi.');
            });
    }
</script>

<style>
    .leaflet-popup-content {
        margin: 8px 12px;
        line-height: 1.4;
    }
    
    .leaflet-popup-content h4 {
        margin: 0 0 4px 0;
    }

    /* Custom marker cursor */
    .leaflet-container {
        cursor: crosshair;
    }

    .leaflet-marker-icon {
        cursor: move;
    }
</style>
