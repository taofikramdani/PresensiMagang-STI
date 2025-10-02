@extends('layouts.peserta')

@section('title', 'Day-In - Presensi ')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* Simple animations */
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    .animate-pulse {
        animation: pulse 2s infinite;
    }
    
    /* Map overlay animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.8); }
        to { opacity: 1; transform: scale(1); }
    }
    
    @keyframes ripple {
        0% { transform: scale(1); opacity: 1; }
        100% { transform: scale(1.5); opacity: 0; }
    }
    
    #userMarker {
        animation: fadeIn 0.5s ease-out;
    }
    
    #radiusCircle {
        animation: fadeIn 0.8s ease-out;
    }
    
    #directionLine {
        animation: fadeIn 0.6s ease-out;
    }
    
    /* Ripple effect for markers */
    .marker-ripple::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        border: 2px solid currentColor;
        animation: ripple 2s infinite;
    }

    /* Custom SweetAlert styling */
    .swal-wide {
        width: 600px !important;
        max-width: 90vw !important;
    }

    .swal2-html-container {
        text-align: left !important;
    }
    
    /* Status indicator styles */
    .status-indicator {
        transition: all 0.3s ease;
    }
    
    .status-indicator.in-range {
        background-color: rgba(16, 185, 129, 0.1);
        border-left: 3px solid #10b981;
    }
    
    .status-indicator.out-of-range {
        background-color: rgba(239, 68, 68, 0.1);
        border-left: 3px solid #ef4444;
    }
    
    /* Camera Modal Styles */
    #cameraModal.hidden {
        display: none !important;
    }
    
    #cameraModal:not(.hidden) {
        display: flex !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-4 p-4">
    <!-- Header dengan Info Singkat -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Presensi Hari Ini</h1>
                <p class="text-sm text-gray-600">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                <p class="text-xs text-blue-600">
                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $lokasi->nama_lokasi ?? 'Kantor Pusat' }}
                </p>
                @if($jamKerja)
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-clock mr-1"></i>{{ $jamKerja->jam_masuk }} - {{ $jamKerja->jam_keluar }} ({{ $jamKerja->nama_shift }})
                </p>
                @endif
            </div>
            <div class="text-right">
                <p id="realTimeClock" class="text-xl font-bold text-blue-600">
                    {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i:s') }}
                </p>
                <script>
                    // Immediate clock start - no logging
                    const clockElement = document.getElementById('realTimeClock');
                    if (clockElement) {
                        function startJakartaClock() {
                            const now = new Date();
                            const jakarta = new Date(now.getTime() + (7 * 60 * 60 * 1000) + (now.getTimezoneOffset() * 60 * 1000));
                            const timeStr = jakarta.toTimeString().substring(0, 8);
                            clockElement.textContent = timeStr;
                        }
                        
                        startJakartaClock();
                        setInterval(startJakartaClock, 1000);
                    }
                </script>
            </div>
        </div>
    </div>

    <!-- Status Presensi Compact -->
    @if($presensiHariIni ?? false)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="grid grid-cols-2 gap-3 mb-3">
                <!-- Check In -->
                <div class="flex items-center space-x-2 p-3 bg-green-50 rounded-lg">
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-sign-in-alt text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs text-green-600">Masuk</p>
                        <p class="text-sm font-semibold text-green-900">{{ $presensiHariIni->jam_masuk }}</p>
                    </div>
                </div>

                <!-- Check Out -->
                <div class="flex items-center space-x-2 p-3 {{ $presensiHariIni->jam_keluar ? 'bg-blue-50' : 'bg-gray-50' }} rounded-lg">
                    <div class="w-8 h-8 {{ $presensiHariIni->jam_keluar ? 'bg-blue-500' : 'bg-gray-400' }} rounded-full flex items-center justify-center">
                        <i class="fas fa-sign-out-alt text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs {{ $presensiHariIni->jam_keluar ? 'text-blue-600' : 'text-gray-500' }}">
                            {{ $presensiHariIni->jam_keluar ? 'Keluar (Terakhir)' : 'Keluar' }}
                        </p>
                        <p class="text-sm font-semibold {{ $presensiHariIni->jam_keluar ? 'text-blue-900' : 'text-gray-500' }}">
                            {{ $presensiHariIni->jam_keluar ?? 'Belum' }}
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Status dan Info Tambahan -->
            <div class="grid grid-cols-3 gap-2 text-xs">
                <div class="text-center p-2 bg-gray-50 rounded">
                    <p class="text-gray-500">Status</p>
                    <p class="font-semibold {{ $presensiHariIni->status === 'hadir' ? 'text-green-600' : ($presensiHariIni->status === 'terlambat' ? 'text-orange-600' : 'text-red-600') }}">
                        {{ $presensiHariIni->getStatusLabel() }}
                    </p>
                </div>
                
                @if($presensiHariIni->keterlambatan > 0)
                <div class="text-center p-2 bg-orange-50 rounded">
                    <p class="text-orange-500">Keterlambatan</p>
                    <p class="font-semibold text-orange-600">{{ $presensiHariIni->getKeterlambatanFormatted() }}</p>
                </div>
                @endif
                
                @if($presensiHariIni->durasi_kerja)
                <div class="text-center p-2 bg-purple-50 rounded">
                    <p class="text-purple-500">Durasi Kerja</p>
                    <p class="font-semibold text-purple-600">{{ $presensiHariIni->getDurasiKerjaFormatted() }}</p>
                </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Tombol Presensi -->
    <div class="grid grid-cols-2 gap-3">
        <!-- Tombol Absen Masuk -->
        <button onclick="checkIn()" id="checkInBtn" 
                class="bg-green-600 text-white font-semibold py-4 px-4 rounded-xl shadow-sm disabled:opacity-50 disabled:cursor-not-allowed {{ ($presensiHariIni && $presensiHariIni->jam_masuk) ? 'opacity-50' : '' }}"
                {{ ($presensiHariIni && $presensiHariIni->jam_masuk) ? 'disabled' : '' }}>
            <div class="flex flex-col items-center space-y-1">
                <i class="fas fa-sign-in-alt text-lg"></i>
                <span class="text-sm">Check In</span>
            </div>
        </button>

        <!-- Tombol Absen Keluar -->
        <button onclick="checkOut()" id="checkOutBtn"
                class="bg-blue-600 text-white font-semibold py-4 px-4 rounded-xl shadow-sm disabled:opacity-50 disabled:cursor-not-allowed {{ (!$presensiHariIni || !$presensiHariIni->jam_masuk) ? 'opacity-50' : '' }}"
                {{ (!$presensiHariIni || !$presensiHariIni->jam_masuk) ? 'disabled data-no-checkin' : '' }}>
            <div class="flex flex-col items-center space-y-1">
                <i class="fas fa-sign-out-alt text-lg"></i>
                <span class="text-sm">
                    @if($presensiHariIni && $presensiHariIni->jam_keluar)
                        Checkout Ulang
                    @else
                        Checkout
                    @endif
                </span>
            </div>
        </button>
    </div>

    <!-- Lokasi Compact -->
    <div class="bg-white rounded-xl  p-4">
        <h2 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
            <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
            Lokasi Magang Anda
        </h2>
        
        @if($peserta && $peserta->lokasi)
        <div class="mb-3 p-2 bg-blue-50 rounded-lg border-l-4 border-blue-500">
            <p class="text-xs text-blue-700">
                <i class="fas fa-info-circle mr-1"></i>
                Lokasi presensi telah ditentukan sesuai tempat magang Anda
            </p>
        </div>
        @else
        <div class="mb-3 p-2 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
            <p class="text-xs text-yellow-700">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Belum ada lokasi magang yang ditentukan. Hubungi admin.
            </p>
        </div>
        @endif
        
        <!-- Leaflet Map Container -->
        <div class="w-full h-48 rounded-lg border border-gray-300 mb-3 bg-gray-100 relative overflow-hidden">
            <div id="map" class="w-full h-full rounded-lg"></div>
            
            <!-- Map Controls -->
            <div class="absolute top-2 right-2 flex space-x-1 z-[1000]">
                <button onclick="centerOnOffice()" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs">
                    <i class="fas fa-crosshairs mr-1"></i>Pusat
                </button>
                <button onclick="getCurrentLocation()" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs">
                    <i class="fas fa-location-arrow mr-1"></i>GPS
                </button>
            </div>
            
            <!-- Status -->
            <div class="absolute bottom-2 right-2 bg-white bg-opacity-95 rounded px-2 py-1 text-xs z-[1000]">
                <span id="locationStatus" class="text-gray-600 font-medium">
                    <i class="fas fa-satellite-dish mr-1"></i>Mencari GPS...
                </span>
            </div>
        </div>
        
        <!-- Info Lokasi Detail -->
        <div class="bg-gray-50 rounded-lg p-3 mb-3">
            <div class="flex items-start space-x-2">
                <i class="fas fa-map-marker-alt text-red-600 mt-1"></i>
                <div>
                    <p class="text-gray-700 font-medium">{{ $lokasi->nama_lokasi ?? 'Kantor Pusat' }}</p>
                    @if($peserta && $peserta->lokasi && $peserta->lokasi->alamat)
                    <p class="text-xs text-gray-500 mt-1">{{ $peserta->lokasi->alamat }}</p>
                    @endif
                    <p class="text-xs text-blue-600 mt-1">
                        <i class="fas fa-bullseye mr-1"></i>Radius: {{ $lokasi->radius ?? 100 }}m
                    </p>
                </div>
            </div>
        </div>
            
            <!-- Loading overlay (will be hidden) -->
            <div id="mapLoading" class="absolute inset-0 bg-gray-100 flex items-center justify-center z-10" style="display: none;">
                <div class="text-center">
                    <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                    <p class="text-gray-500 text-sm">Loading map...</p>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg status-indicator border-l-4 border-blue-500">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-satellite-dish text-gray-500"></i>
                    <span class="text-sm text-gray-600 font-medium">GPS Status:</span>
                </div>
                <span id="gpsStatus" class="text-sm font-medium text-yellow-600">Menunggu...</span>
            </div>
            
            <!-- Garis Pemisah -->
            <div class="border-t border-gray-200"></div>
            
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg status-indicator border-l-4 border-green-500">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-ruler text-gray-500"></i>
                    <span class="text-sm text-gray-600 font-medium">Jarak ke Kantor:</span>
                </div>
                <span id="distanceStatus" class="text-sm font-medium text-gray-600">Menghitung...</span>
            </div>
        </div>
        
        <!-- Location Details -->
        <div id="locationDetails" class="mt-3 p-3 bg-blue-50 rounded-lg hidden">
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                    <span class="text-blue-600 font-medium">Koordinat Anda:</span>
                    <p id="userCoordinates" class="text-blue-900">-</p>
                </div>
                <div>
                    <span class="text-blue-600 font-medium">Arah ke Kantor:</span>
                    <p id="bearingInfo" class="text-blue-900">-</p>
                </div>
            </div>
        </div>
        
        <!-- GPS Permission Button (Hidden by default) -->
        <button id="enableGpsBtn" onclick="requestGPSPermission()" class="hidden w-full mt-2 bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg text-sm transition-colors">
            <i class="fas fa-location-arrow mr-2"></i>
            Izinkan Akses Lokasi GPS
        </button>
    </div>

    <!-- Camera Modal -->
    <div id="cameraModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-50 items-center justify-center p-4" style="z-index: 9999;">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ambil Foto Presensi</h3>
            
            <div class="space-y-4">
                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden relative">
                    <video id="camera" class="w-full h-full object-cover bg-black" autoplay muted playsinline></video>
                    <canvas id="photoCanvas" class="hidden"></canvas>
                </div>
                
                <div class="flex space-x-3">
                    <button onclick="takePicture()" class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-camera mr-2"></i>
                        Ambil Foto
                    </button>
                    <button onclick="closeCameraModal()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg hover:bg-gray-400 transition-colors duration-200">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- SweetAlert2 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Variables
    let map;
    let userLocation = null;
    let officeMarker;
    let userMarker;
    let radiusCircle;
    let connectionLine;
    let officeLocation = { 
        lat: {{ $lokasi->latitude ?? -6.2088 }}, 
        lng: {{ $lokasi->longitude ?? 106.8456 }},
        radius: {{ $lokasi->radius ?? 100 }},
        name: "{{ $lokasi->nama_lokasi ?? 'Kantor Pusat' }}"
    };
    let cameraStream = null;
    let currentAction = null;
    let isHttps = false;
    let useDefaultLocation = false;
    let defaultLocation = null;

    // Helper function to get Jakarta time
    function getJakartaTime() {
        const now = new Date();
        const jakarta = new Date(now.getTime() + (7 * 60 * 60 * 1000) + (now.getTimezoneOffset() * 60 * 1000));
        return jakarta;
    }

    // Helper function to format Jakarta time
    function formatJakartaTime(date = null) {
        const jakartaTime = date || getJakartaTime();
        const hours = String(jakartaTime.getHours()).padStart(2, '0');
        const minutes = String(jakartaTime.getMinutes()).padStart(2, '0');
        const seconds = String(jakartaTime.getSeconds()).padStart(2, '0');
        return `${hours}:${minutes}:${seconds}`;
    }

    // Check SSL status
    async function checkSSLStatus() {
        try {
            isHttps = window.location.protocol === 'https:';
            console.log('SSL Status:', isHttps ? 'HTTPS enabled' : 'HTTP only');
            return isHttps;
        } catch (error) {
            console.error('Error checking SSL status:', error);
            return false;
        }
    }

    // Get default location from server
    async function getDefaultLocation() {
        try {
            const response = await fetch('{{ route("peserta.presensi.default-location") }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to get default location');
            }

            const result = await response.json();
            if (result.success) {
                defaultLocation = result.data;
                console.log('Default location loaded:', defaultLocation);
                return defaultLocation;
            } else {
                throw new Error(result.message || 'Failed to get default location');
            }
        } catch (error) {
            console.error('Error getting default location:', error);
            return null;
        }
    }

    // Initialize location based on SSL status
    async function initializeLocation() {
        await checkSSLStatus();
        await getDefaultLocation();

        if (isHttps) {
            console.log('HTTPS detected - Using GPS location');
            document.getElementById('locationStatus').innerHTML = 
                '<i class="fas fa-satellite-dish mr-1"></i>Menggunakan GPS...';
            getCurrentLocation();
        } else {
            console.log('HTTP detected - Using default location');
            useDefaultLocation = true;
            
            if (defaultLocation) {
                userLocation = {
                    lat: parseFloat(defaultLocation.latitude),
                    lng: parseFloat(defaultLocation.longitude),
                    accuracy: 0
                };
                
                document.getElementById('locationStatus').innerHTML = 
                    '<i class="fas fa-map-marker-alt mr-1 text-blue-600"></i>Lokasi Default';
                    
                updateLocationDisplay();
                updateUserMarker();
                calculateDistance();
            } else {
                document.getElementById('locationStatus').innerHTML = 
                    '<i class="fas fa-exclamation-triangle mr-1 text-red-600"></i>Lokasi tidak tersedia';
            }
        }
    }

    // Initialize Leaflet Map
    function initMap() {
        // Create map
        map = L.map('map').setView([officeLocation.lat, officeLocation.lng], 17);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add office marker
        const officeIcon = L.divIcon({
            className: 'office-marker',
            html: '<div style="background: #dc2626; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="fas fa-building"></i></div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        });

        officeMarker = L.marker([officeLocation.lat, officeLocation.lng], { icon: officeIcon })
            .addTo(map)
            .bindPopup(`<strong>${officeLocation.name}</strong><br>Radius: ${officeLocation.radius}m`);

        // Add radius circle
        radiusCircle = L.circle([officeLocation.lat, officeLocation.lng], {
            color: '#dc2626',
            fillColor: '#dc2626',
            fillOpacity: 0.2,
            radius: officeLocation.radius,
            weight: 3,
            dashArray: '10, 10'
        }).addTo(map);

        // Start GPS
        initializeLocation();
    }

    // Get current location
    function getCurrentLocation() {
        // Only try GPS if HTTPS is available
        if (!isHttps) {
            console.log('GPS not available - using default location');
            document.getElementById('locationStatus').innerHTML = 
                '<i class="fas fa-info-circle mr-1 text-blue-600"></i>Menggunakan lokasi default';
            return;
        }

        document.getElementById('locationStatus').innerHTML = 
            '<i class="fas fa-satellite-dish mr-1"></i>Mencari GPS...';
            
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                position => {
                    userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    };
                    
                    console.log('GPS location found:', userLocation);
                    
                    updateUserMarker();
                    calculateDistance();
                    updateMapView();
                    updateLocationDisplay();
                    
                    const accuracy = position.coords.accuracy < 20 ? 'Tinggi' : 'Rendah';
                    document.getElementById('locationStatus').innerHTML = 
                        `<i class="fas fa-check-circle mr-1 text-green-600"></i>GPS ${accuracy}`;
                },
                error => {
                    console.error('GPS Error:', error);
                    let errorMsg = 'GPS Error';
                    
                    if (error.code === 1) {
                        errorMsg = 'Izin ditolak - menggunakan lokasi default';
                    } else if (error.code === 2) {
                        errorMsg = 'Posisi tidak tersedia - menggunakan lokasi default';
                    } else if (error.code === 3) {
                        errorMsg = 'Timeout - menggunakan lokasi default';
                    }
                    
                    document.getElementById('locationStatus').innerHTML = 
                        `<i class="fas fa-exclamation-triangle mr-1 text-orange-600"></i>${errorMsg}`;
                    
                    // Fallback to default location if GPS fails
                    useDefaultLocationFallback();
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000
                }
            );
        } else {
            document.getElementById('locationStatus').innerHTML = 
                '<i class="fas fa-times-circle mr-1 text-red-600"></i>GPS Tidak Didukung - menggunakan lokasi default';
            useDefaultLocationFallback();
        }
    }

    // Fallback to default location when GPS fails
    function useDefaultLocationFallback() {
        useDefaultLocation = true;
        
        if (defaultLocation) {
            userLocation = {
                lat: parseFloat(defaultLocation.latitude),
                lng: parseFloat(defaultLocation.longitude),
                accuracy: 0
            };
            
            console.log('Using default location fallback:', userLocation);
            
            updateUserMarker();
            calculateDistance();
            updateMapView();
            updateLocationDisplay();
        }
    }

    // Update location display in UI
    function updateLocationDisplay() {
        const gpsStatusElement = document.getElementById('gpsStatus');
        if (gpsStatusElement) {
            if (useDefaultLocation) {
                gpsStatusElement.innerHTML = '<span class="text-blue-600 font-medium">Lokasi Default</span>';
            } else {
                gpsStatusElement.innerHTML = '<span class="text-green-600 font-medium">GPS Aktif</span>';
            }
        }

        // Show location details if element exists
        const locationDetails = document.getElementById('locationDetails');
        if (locationDetails && userLocation) {
            locationDetails.classList.remove('hidden');
            
            const userCoordinates = document.getElementById('userCoordinates');
            const userAccuracy = document.getElementById('userAccuracy');
            
            if (userCoordinates) {
                userCoordinates.textContent = `${userLocation.lat.toFixed(6)}, ${userLocation.lng.toFixed(6)}`;
            }
            
            if (userAccuracy) {
                userAccuracy.textContent = useDefaultLocation ? 'Default' : `±${Math.round(userLocation.accuracy)}m`;
            }
        }
    }

    // Update user marker on map
    function updateUserMarker() {
        if (!userLocation || !map) return;

        // Remove existing user marker
        if (userMarker) {
            map.removeLayer(userMarker);
        }
        
        // Remove existing connection line
        if (connectionLine) {
            map.removeLayer(connectionLine);
        }

        // Create user marker
        const userIcon = L.divIcon({
            className: 'user-marker',
            html: '<div style="background: #3b82f6; color: white; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="fas fa-user"></i></div>',
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });

        userMarker = L.marker([userLocation.lat, userLocation.lng], { icon: userIcon })
            .addTo(map)
            .bindPopup(`Lokasi Anda<br>Akurasi: ${Math.round(userLocation.accuracy)}m`);

        // Add connection line
        connectionLine = L.polyline([
            [officeLocation.lat, officeLocation.lng],
            [userLocation.lat, userLocation.lng]
        ], {
            color: '#3b82f6',
            weight: 2,
            opacity: 0.7,
            dashArray: '8, 8'
        }).addTo(map);
    }

    // Update map view to show both markers
    function updateMapView() {
        if (!userLocation || !map) return;

        const group = new L.featureGroup([officeMarker, userMarker]);
        map.fitBounds(group.getBounds().pad(0.1));
    }

    // Center map on office
    function centerOnOffice() {
        if (map) {
            map.setView([officeLocation.lat, officeLocation.lng], 17);
        }
    }

    // Calculate distance
    function calculateDistance() {
        if (!userLocation) return;

        const distance = getDistanceFromLatLonInM(
            userLocation.lat, userLocation.lng,
            officeLocation.lat, officeLocation.lng
        );

        // Update distance display
        const distanceElement = document.getElementById('distanceStatus');
        if (distanceElement) {
            const isInRange = distance <= officeLocation.radius;
            const icon = isInRange ? 'fa-check-circle' : 'fa-exclamation-triangle';
            const color = isInRange ? 'text-green-600' : 'text-red-600';
            const status = isInRange ? 'Dalam jangkauan' : 'Terlalu jauh';
            
            distanceElement.innerHTML = `
                <div class="flex items-center ${color}">
                    <i class="fas ${icon} mr-2"></i>
                    <span class="font-semibold">${Math.round(distance)}m dari kantor</span>
                </div>
                <div class="text-xs ${isInRange ? 'text-green-500' : 'text-red-500'} mt-1">
                    ${status} untuk presensi (max ${officeLocation.radius}m)
                </div>
            `;
        }

        // Update GPS coordinates display if element exists
        const userCoordinates = document.getElementById('userCoordinates');
        if (userCoordinates) {
            userCoordinates.textContent = `${userLocation.lat.toFixed(6)}, ${userLocation.lng.toFixed(6)}`;
        }

        // Enable/disable buttons based on distance
        const checkInBtn = document.getElementById('checkInBtn');
        const checkOutBtn = document.getElementById('checkOutBtn');
        const isInRange = distance <= officeLocation.radius;
        
        if (checkInBtn && !checkInBtn.hasAttribute('data-already-checked')) {
            if (isInRange) {
                checkInBtn.classList.remove('opacity-50');
                checkInBtn.disabled = false;
            } else {
                checkInBtn.classList.add('opacity-50');
                checkInBtn.disabled = true;
            }
        }
        
        if (checkOutBtn) {
            // Checkout button tetap bisa digunakan selama sudah check-in (untuk checkout ulang)
            const sudahCheckIn = !checkOutBtn.hasAttribute('data-no-checkin');
            if (isInRange && sudahCheckIn) {
                checkOutBtn.classList.remove('opacity-50');
                checkOutBtn.disabled = false;
            } else if (!sudahCheckIn) {
                checkOutBtn.classList.add('opacity-50');
                checkOutBtn.disabled = true;
            } else {
                checkOutBtn.classList.add('opacity-50');
                checkOutBtn.disabled = true;
            }
        }

        return distance;
    }

    // Calculate distance between two coordinates
    function getDistanceFromLatLonInM(lat1, lon1, lat2, lon2) {
        const R = 6371; // Radius of the earth in km
        const dLat = deg2rad(lat2 - lat1);
        const dLon = deg2rad(lon2 - lon1);
        const a = 
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
            Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        const d = R * c; // Distance in km
        return d * 1000; // Convert to meters
    }

    function deg2rad(deg) {
        return deg * (Math.PI/180);
    }

    // Check in function
    function checkIn() {
        if (!userLocation) {
            Swal.fire({
                icon: 'warning',
                title: 'Lokasi Belum Siap',
                text: 'Lokasi belum ditemukan. Mohon tunggu lokasi dimuat.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        // If using default location, skip distance check
        if (!useDefaultLocation) {
            const distance = calculateDistance();
            if (distance > officeLocation.radius) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lokasi Terlalu Jauh',
                    text: `Anda terlalu jauh dari kantor (${Math.round(distance)}m). Maksimal ${officeLocation.radius}m.`,
                    confirmButtonColor: '#d33'
                });
                return;
            }
        }
        
        currentAction = 'checkin';
        
        // If HTTPS is available, use camera
        if (isHttps) {
            openCameraModal();
        } else {
            // Skip camera and directly submit without photo
            submitPresensiWithoutPhoto();
        }
    }

    // Check out function  
    function checkOut() {
        if (!userLocation) {
            Swal.fire({
                icon: 'warning',
                title: 'Lokasi Belum Siap',
                text: 'Lokasi belum ditemukan. Mohon tunggu lokasi dimuat.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        // If using default location, skip distance check
        if (!useDefaultLocation) {
            const distance = calculateDistance();
            if (distance > officeLocation.radius) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lokasi Terlalu Jauh',
                    text: `Anda terlalu jauh dari kantor (${Math.round(distance)}m). Maksimal ${officeLocation.radius}m.`,
                    confirmButtonColor: '#d33'
                });
                return;
            }
        }
        
        // Cek kegiatan harian sebelum checkout
        checkKegiatanHarian();
    }

    // Fungsi untuk mengecek kegiatan harian
    async function checkKegiatanHarian() {
        // Show loading
        Swal.fire({
            title: 'Mengecek Kegiatan...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const response = await fetch('/peserta/check-kegiatan-harian', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            // Close loading
            Swal.close();

            if (result.success) {
                if (result.hasKegiatan) {
                    // Sudah ada kegiatan, lanjut checkout
                    currentAction = 'checkout';
                    
                    // If HTTPS is available, use camera
                    if (isHttps) {
                        openCameraModal();
                    } else {
                        // Skip camera and directly submit without photo
                        submitPresensiWithoutPhoto();
                    }
                } else {
                    // Belum ada kegiatan, tampilkan warning
                    Swal.fire({
                        icon: 'warning',
                        title: 'Kegiatan Belum Diisi!',
                        html: `
                            <div class="text-center">
                                <p class="mb-3">Anda belum mengisi kegiatan untuk hari ini.</p>
                                <p class="mb-3 text-sm text-gray-600">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Minimal 1 kegiatan harus diisi sebelum checkout.
                                </p>
                            </div>
                        `,
                        showCancelButton: false,
                        confirmButtonText: '<i class="fas fa-plus mr-2"></i>Isi Kegiatan Sekarang',
                        confirmButtonColor: '#10b981',
                        customClass: {
                            popup: 'swal-wide'
                        },
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect ke halaman kegiatan
                            window.location.href = '/peserta/kegiatan';
                        }
                    });
                }
            } else {
                // Error saat cek kegiatan, tampilkan pesan error dan tidak lanjut checkout
                console.error('Error checking kegiatan:', result.message);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mengecek Kegiatan',
                    text: 'Tidak dapat mengecek status kegiatan. Silakan coba lagi.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6'
                });
            }
        } catch (error) {
            console.error('Error checking kegiatan:', error);
            // Close loading jika masih aktif
            Swal.close();
            
            // Jika error koneksi, tampilkan pesan error dan tidak lanjut checkout
            Swal.fire({
                icon: 'error',
                title: 'Koneksi Bermasalah',
                text: 'Tidak dapat mengecek status kegiatan. Periksa koneksi internet dan coba lagi.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6'
            });
        }
    }

    // Submit presensi without photo (for HTTP mode)
    async function submitPresensiWithoutPhoto() {
        if (!userLocation) {
            Swal.fire({
                icon: 'warning',
                title: 'Lokasi Belum Siap',
                text: 'Lokasi belum ditemukan. Mohon tunggu lokasi dimuat.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Show loading
        Swal.fire({
            title: 'Memproses Presensi...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('latitude', userLocation.lat);
        formData.append('longitude', userLocation.lng);
        formData.append('action', currentAction);
        formData.append('use_default_location', useDefaultLocation ? '1' : '0');
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        try {
            const response = await fetch('{{ route("peserta.presensi.store") }}', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Presensi Berhasil!',
                    html: `
                        <div class="text-center">
                            <p><strong>Waktu:</strong> ${result.data.jam_masuk || result.data.jam_keluar}</p>
                            <p><strong>Status:</strong> ${result.data.status || 'Berhasil'}</p>
                            ${result.data.keterlambatan ? `<p><strong>Keterlambatan:</strong> ${result.data.keterlambatan}</p>` : ''}
                            ${result.data.durasi_kerja ? `<p><strong>Durasi Kerja:</strong> ${result.data.durasi_kerja}</p>` : ''}
                        </div>
                    `,
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Presensi Gagal',
                    text: result.message,
                    confirmButtonColor: '#d33'
                });
            }
        } catch (error) {
            console.error('Error submitting presensi:', error);
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Gagal memproses presensi. Silakan coba lagi.',
                confirmButtonColor: '#d33'
            });
        }
    }

    // Camera modal functions
    function openCameraModal() {
        const modal = document.getElementById('cameraModal');
        if (modal) {
            // Hide page content to prevent bleed-through
            document.body.style.overflow = 'hidden';
            
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.zIndex = '9999';
            
            startCamera();
        }
    }

    function closeCameraModal() {
        const modal = document.getElementById('cameraModal');
        if (modal) {
            // Restore page scroll
            document.body.style.overflow = '';
            
            modal.classList.add('hidden');
            modal.style.display = 'none';
            stopCamera();
        }
    }

    async function startCamera() {
        try {
            // Request camera permission
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'user',
                    width: { min: 640, ideal: 1280, max: 1920 },
                    height: { min: 480, ideal: 720, max: 1080 }
                } 
            });
            
            const video = document.getElementById('camera');
            if (video) {
                video.srcObject = stream;
                cameraStream = stream;
                
                // Wait for video to be ready
                video.addEventListener('loadedmetadata', () => {
                    video.play();
                });
                
                console.log('Camera started successfully');
            } else {
                throw new Error('Video element not found');
            }
        } catch (error) {
            console.error('Camera error:', error);
            
            let errorMessage = 'Gagal mengakses kamera: ';
            if (error.name === 'NotAllowedError') {
                errorMessage += 'Izin kamera ditolak. Silakan izinkan akses kamera di browser.';
            } else if (error.name === 'NotFoundError') {
                errorMessage += 'Kamera tidak ditemukan.';
            } else if (error.name === 'NotSupportedError') {
                errorMessage += 'Browser tidak mendukung akses kamera.';
            } else {
                errorMessage += error.message;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error Kamera',
                text: errorMessage,
                confirmButtonColor: '#d33'
            });
        }
    }

    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
    }

    function capturePhoto() {
        if (!cameraStream) {
            Swal.fire({
                icon: 'warning',
                title: 'Kamera Tidak Aktif',
                text: 'Kamera belum aktif',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        const video = document.getElementById('camera');
        if (!video) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Video element tidak ditemukan',
                confirmButtonColor: '#d33'
            });
            return;
        }

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        
        canvas.toBlob(blob => {
            submitPresensi(blob);
        }, 'image/jpeg', 0.8);
    }

    // Alias untuk tombol di HTML
    function takePicture() {
        capturePhoto();
    }

    async function submitPresensi(photoBlob) {
        if (!userLocation) {
            Swal.fire({
                icon: 'warning',
                title: 'Lokasi Tidak Ditemukan',
                text: 'Lokasi tidak ditemukan',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Show loading
        Swal.fire({
            title: 'Memproses Presensi...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('latitude', userLocation.lat);
        formData.append('longitude', userLocation.lng);
        formData.append('action', currentAction);
        formData.append('photo', photoBlob, 'presensi.jpg');
        formData.append('use_default_location', useDefaultLocation ? '1' : '0');
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        try {
            const response = await fetch('{{ route("peserta.presensi.store") }}', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                const actionText = currentAction === 'checkin' ? 'Check-in' : 'Check-out';
                
                // Gunakan data dari response controller
                const responseData = result.data || {};
                const jamPresensi = responseData.jam_masuk || responseData.jam_keluar || formatJakartaTime();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: `<div>
                        <div class="mb-3 p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600">${actionText} berhasil dicatat pada</p>
                            <p class="text-lg font-semibold text-green-600">${jamPresensi} WIB</p>
                            ${responseData.status ? `<p class="text-xs text-blue-600 mt-1">Status: ${responseData.status}</p>` : ''}
                            ${responseData.keterlambatan && responseData.keterlambatan !== '-' ? `<p class="text-xs text-orange-600">Keterlambatan: ${responseData.keterlambatan}</p>` : ''}
                            ${responseData.durasi_kerja ? `<p class="text-xs text-purple-600">Durasi Kerja: ${responseData.durasi_kerja}</p>` : ''}
                        </div>
                    </div>`,
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: result.message || 'Terjadi kesalahan',
                    confirmButtonColor: '#d33'
                });
            }
        } catch (error) {
            console.error('Error submitting presensi:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan saat mengirim data',
                confirmButtonColor: '#d33'
            });
        } finally {
            closeCameraModal();
        }
    }

    // Real-time clock
    function updateClock() {
        try {
            const timeString = formatJakartaTime();
            
            const clockElement = document.getElementById('realTimeClock');
            if (clockElement) {
                clockElement.textContent = timeString;
                clockElement.style.color = '#2563eb';
            }
        } catch (error) {
            const clockElement = document.getElementById('realTimeClock');
            if (clockElement) {
                const simple = new Date().toTimeString().split(' ')[0];
                clockElement.textContent = simple;
            }
        }
    }

    // Initialize everything
    document.addEventListener('DOMContentLoaded', function() {
        // Start clock
        updateClock();
        setInterval(updateClock, 1000);
        
        // Initialize map
        setTimeout(() => {
            initMap();
        }, 500);
        
        // Auto refresh location every 30 seconds (only if HTTPS)
        setInterval(() => {
            if (isHttps) {
                getCurrentLocation();
            }
        }, 30000);
    });
</script>
@endpush
