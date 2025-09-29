@extends('layouts.main')

@section('title', 'Manajemen Lokasi')

@section('content')
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Manajemen Lokasi</h1>
                    <p class="text-gray-600 mt-1">Kelola lokasi presensi dengan detail koordinat dan radius</p>
                </div>
                @if($user->role === 'admin')
                    <a href="{{ route('admin.lokasi.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Lokasi
                    </a>
                @endif
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <!-- Data Section -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-list-ul mr-2 text-blue-600"></i>
                    Daftar Lokasi
                </h2>
            </div>
                    
                    <div class="divide-y divide-gray-200">
                        @forelse($lokasi as $index => $item)
                            <div>
                                <!-- Accordion Header -->
                                <button 
                                    class="w-full px-4 py-4 sm:px-6 text-left bg-white hover:bg-gray-50 focus:outline-none focus:bg-gray-50 transition-colors duration-200"
                                    data-accordion-index="{{ $index }}"
                                    id="accordion-button-{{ $index }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r {{ $item->is_active ? 'from-green-500 to-green-600' : 'from-gray-400 to-gray-500' }} rounded-full flex items-center justify-center">
                                                    <i class="fas fa-map-marker-alt text-white text-sm sm:text-base"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-sm sm:text-base font-medium text-gray-900 truncate">{{ $item->nama_lokasi }}</h3>
                                                <p class="text-xs sm:text-sm text-gray-500 truncate">{{ Str::limit($item->alamat, 40) }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2 flex-shrink-0">
                                            <span class="hidden sm:inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $item->getStatusDisplayName() }}
                                            </span>
                                            <!-- Mobile status indicator -->
                                            <div class="sm:hidden w-3 h-3 rounded-full {{ $item->is_active ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                            <i class="fas fa-chevron-down transform transition-transform duration-200 text-gray-400" id="accordion-icon-{{ $index }}"></i>
                                        </div>
                                    </div>
                                </button>

                                <!-- Accordion Content -->
                                <div 
                                    class="hidden px-4 sm:px-6 pb-4 bg-gray-50" 
                                    id="accordion-content-{{ $index }}">
                                    <div class="space-y-4 pt-3">
                                        <!-- Mobile Status (only visible on mobile) -->
                                        <div class="sm:hidden">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $item->getStatusDisplayName() }}
                                            </span>
                                        </div>
                                        
                                        <!-- Info Detail -->
                                        <div class="space-y-3">
                                            <div class="flex flex-col sm:flex-row sm:justify-between">
                                                <span class="font-medium text-gray-600 text-sm">Alamat:</span>
                                                <span class="text-gray-900 text-sm mt-1 sm:mt-0 sm:text-right sm:max-w-xs">{{ $item->alamat }}</span>
                                            </div>
                                            <div class="flex flex-col sm:flex-row sm:justify-between">
                                                <span class="font-medium text-gray-600 text-sm">Koordinat:</span>
                                                <span class="text-gray-900 font-mono text-sm mt-1 sm:mt-0">{{ $item->coordinates }}</span>
                                            </div>
                                            <div class="flex flex-col sm:flex-row sm:justify-between">
                                                <span class="font-medium text-gray-600 text-sm">Radius:</span>
                                                <span class="text-gray-900 text-sm mt-1 sm:mt-0">{{ $item->radius }} meter</span>
                                            </div>
                                            @if($item->keterangan)
                                                <div class="flex flex-col sm:flex-row sm:justify-between">
                                                    <span class="font-medium text-gray-600 text-sm">Keterangan:</span>
                                                    <span class="text-gray-900 text-sm mt-1 sm:mt-0 sm:text-right sm:max-w-xs">{{ $item->keterangan }}</span>
                                                </div>
                                            @endif
                                            <div class="flex flex-col sm:flex-row sm:justify-between">
                                                <span class="font-medium text-gray-600 text-sm">Dibuat:</span>
                                                <span class="text-gray-900 text-sm mt-1 sm:mt-0">{{ $item->created_at->format('d M Y H:i') }}</span>
                                            </div>
                                        </div>

                                        <!-- Map Section -->
                                        <div class="pt-4 border-t border-gray-200">
                                            <h4 class="text-sm font-medium text-gray-800 mb-3 flex items-center">
                                                <i class="fas fa-map mr-2 text-green-600"></i>
                                                Lokasi di Peta
                                            </h4>
                                            <div class="rounded-lg overflow-hidden border border-gray-200">
                                                <div id="map-{{ $index }}" class="h-48 sm:h-64 w-full"></div>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        @if($user->role === 'admin')
                                            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 pt-4 border-t border-gray-200">
                                                <a href="{{ route('admin.lokasi.edit', $item->id) }}" 
                                                   class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium py-2.5 px-4 rounded-md transition duration-200 text-center">
                                                    <i class="fas fa-edit mr-2"></i>
                                                    Edit Lokasi
                                                </a>
                                                <form action="{{ route('admin.lokasi.destroy', $item->id) }}" method="POST" class="flex-1"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="w-full bg-red-500 hover:bg-red-600 text-white text-sm font-medium py-2.5 px-4 rounded-md transition duration-200">
                                                        <i class="fas fa-trash mr-2"></i>
                                                        Hapus Lokasi
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-500">
                                <i class="fas fa-map-marker-alt text-4xl mb-4 text-gray-300"></i>
                                <p class="text-lg font-medium">Belum ada data lokasi</p>
                                <p class="text-sm">Tambahkan lokasi presensi untuk memulai</p>
                            </div>
                        @endforelse
                    </div>
                </div>
        </div>
    </div>
@endsection

<!-- Include Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let maps = {};
    let markers = {};

    // Initialize when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, accordion ready');
        
        // Add event listeners to all accordion buttons
        document.querySelectorAll('[data-accordion-index]').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-accordion-index'));
                toggleAccordion(index);
            });
        });
    });

    function toggleAccordion(index) {
        console.log('Toggle accordion:', index);
        
        const content = document.getElementById(`accordion-content-${index}`);
        const icon = document.getElementById(`accordion-icon-${index}`);
        
        if (!content || !icon) {
            console.error('Elements not found for index:', index);
            return;
        }

        const isHidden = content.classList.contains('hidden');
        
        // Close all other accordions first
        document.querySelectorAll('[id^="accordion-content-"]').forEach((el, idx) => {
            if (el.id !== `accordion-content-${index}`) {
                el.classList.add('hidden');
            }
        });
        
        document.querySelectorAll('[id^="accordion-icon-"]').forEach((el, idx) => {
            if (el.id !== `accordion-icon-${index}`) {
                el.classList.remove('rotate-180');
            }
        });
        
        // Toggle current accordion
        if (isHidden) {
            content.classList.remove('hidden');
            icon.classList.add('rotate-180');
            console.log('Opening accordion:', index);
            
            // Initialize map for this accordion if not already done
            if (!maps[index]) {
                setTimeout(() => {
                    initializeAccordionMap(index);
                }, 150);
            }
        } else {
            content.classList.add('hidden');
            icon.classList.remove('rotate-180');
            console.log('Closing accordion:', index);
        }
    }

    function initializeAccordionMap(index) {
        const mapId = `map-${index}`;
        const mapElement = document.getElementById(mapId);
        
        if (!mapElement) {
            console.error('Map element not found:', mapId);
            return;
        }

        console.log('Initializing map for index:', index);

        // Location data array
        const locationData = {
            @foreach($lokasi as $loopIndex => $item)
            {{ $loopIndex }}: {
                lat: {{ $item->latitude }},
                lng: {{ $item->longitude }},
                radius: {{ $item->radius }},
                isActive: {{ $item->is_active ? 'true' : 'false' }},
                name: "{{ addslashes($item->nama_lokasi) }}",
                address: "{{ addslashes(Str::limit($item->alamat, 50)) }}",
                coordinates: "{{ $item->coordinates }}"
            }@if(!$loop->last),@endif
            @endforeach
        };

        const data = locationData[index];
        if (!data) {
            console.error('No data found for index:', index);
            return;
        }

        // Create map
        const map = L.map(mapId).setView([data.lat, data.lng], 16);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Add marker
        const marker = L.marker([data.lat, data.lng]).addTo(map)
            .bindPopup(`
                <div class="text-sm">
                    <h4 class="font-semibold mb-1">${data.name}</h4>
                    <p class="text-gray-600 mb-1">${data.address}</p>
                    <p class="text-xs text-gray-500">Radius: ${data.radius}m</p>
                    <p class="text-xs font-mono text-gray-500">${data.coordinates}</p>
                </div>
            `).openPopup();
        
        // Add radius circle
        const circle = L.circle([data.lat, data.lng], {
            color: data.isActive ? 'green' : 'red',
            fillColor: data.isActive ? '#22c55e' : '#ef4444',
            fillOpacity: 0.2,
            radius: data.radius
        }).addTo(map);
        
        // Store map reference
        maps[index] = map;
        markers[index] = { marker, circle };
        
        // Invalidate size after a short delay to ensure proper rendering
        setTimeout(() => {
            map.invalidateSize();
            console.log('Map initialized successfully for index:', index);
        }, 200);
    }
</script>

<style>
    .rotate-180 {
        transform: rotate(180deg);
    }
    
    .leaflet-popup-content {
        margin: 8px 12px;
        line-height: 1.4;
    }
    
    .leaflet-popup-content h4 {
        margin: 0 0 4px 0;
    }
    
    /* Accordion smooth transitions */
    .accordion-content {
        transition: all 0.3s ease-in-out;
    }
    
    /* Custom scrollbar */
    .divide-y::-webkit-scrollbar {
        width: 6px;
    }
    
    .divide-y::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .divide-y::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    .divide-y::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
