<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data: blob:; font-src 'self' https: data:;">
    <title>@yield('title', 'Presensi Peserta - Day-In')</title>
    
    @if(app()->environment('production'))
        <!-- Production/ngrok: Use Tailwind CDN + Vite JS -->
        <script src="https://cdn.tailwindcss.com"></script>
        @vite(['resources/js/app.js'])
    @else
        <!-- Development: Use Vite with built CSS -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    
    <!-- Google Fonts - Public Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    
    <style>
        /* Force font application */
        * {
            font-family: 'Public Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif !important;
        }
        
        body {
            font-family: 'Public Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif !important;
        }
    </style>
    
    <!-- Font Awesome - Multiple reliable sources -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Backup Font Awesome CDN -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.0.0/css/all.css">
    
    <!-- Alternative backup -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- Inline Font Awesome as ultimate fallback -->
    <style>
        /* Force Font Awesome to load */
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
        
        /* Ensure Font Awesome loads properly */
        .fas, .far, .fab, .fa {
            font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
            font-weight: 900 !important;
            font-style: normal !important;
            font-variant: normal !important;
            text-rendering: auto !important;
            line-height: 1 !important;
        }
        
        /* Force display of icons */
        .fas::before, .far::before, .fab::before, .fa::before {
            display: inline-block !important;
        }
    </style>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    @stack('styles')
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Mobile Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo & Title -->
                <div class="flex items-center space-x-3">
                    <!-- Logo Danantara -->
                    <div class="flex items-center space-x-2">
                        <img src="{{ asset('image/Danantara.png') }}" alt="Logo Danantara" class="h-6 w-auto">
                    </div>
                    
                    <!-- Divider -->
                    <div class="h-4 w-px bg-gray-300"></div>
                    
                    <!-- Logo PLN -->
                    <div class="flex items-center space-x-2">
                        <img src="{{ asset('image/PLN.png') }}" alt="Logo PLN" class="h-6 w-auto">
                    </div>
                </div>

                <!-- User Info & Notifications -->
                <div class="flex items-center space-x-3">
                    <!-- Notification Icon -->
                    <div class="relative">
                        <button id="notificationButton" onclick="toggleNotification()" class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors duration-150">
                            <i class="fas fa-bell text-lg"></i>
                            <span id="notificationBadge" class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full hidden"></span>
                        </button>
                        
                        <!-- Notification Dropdown -->
                        <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="font-medium text-gray-900">Notifikasi</h3>
                                <button id="markAllRead" onclick="markAllNotificationsAsRead()" class="text-sm text-blue-600 hover:text-blue-800">
                                    Tandai semua dibaca
                                </button>
                            </div>
                            <div id="notificationList" class="max-h-64 overflow-y-auto">
                                <div class="p-4 text-center text-gray-500 text-sm">
                                    <i class="fas fa-bell-slash text-2xl mb-2"></i>
                                    <p>Memuat notifikasi...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="relative">
                        <button onclick="toggleUserDropdown()" class="flex items-center space-x-2 p-1 rounded-full hover:bg-gray-100 transition-colors duration-150">
                            <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center text-white font-medium text-sm">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </button>
                        
                        <!-- User Dropdown -->
                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <div class="p-4 border-b border-gray-200">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center text-white font-medium">
                                        {{ substr(Auth::user()->peserta->nama_lengkap ?? 'User', 0, 1) }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 text-sm">{{ Auth::user()->peserta->nama_lengkap ?? 'User' }}</p>
                                        <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                                        <p class="text-xs text-blue-600 font-medium">Peserta Magang</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-2">
                                <a href="{{ route('peserta.profile.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors duration-150">
                                    <i class="fas fa-user-circle w-4"></i>
                                    <span>Profil Saya</span>
                                </a>
                                <hr class="my-2">
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="flex items-center space-x-3 w-full px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors duration-150 text-left">
                                        <i class="fas fa-sign-out-alt w-4"></i>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="min-h-screen pb-20">
        @yield('content')
    </main>

    <!-- Bottom Navigation (Mobile) -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40">
        <div class="flex justify-around items-center h-16 px-1">
            <!-- Home -->
            <a href="{{ route('peserta.dashboard') }}" 
               class="flex flex-col items-center justify-center space-y-1 flex-1 h-full {{ request()->routeIs('peserta.dashboard') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }} transition-colors duration-150">
                <i class="fas fa-home text-base"></i>
                <span class="text-xs font-medium">Home</span>
            </a>
            
            <!-- Presensi -->
            <a href="{{ route('peserta.presensi.index') }}" 
               class="flex flex-col items-center justify-center space-y-1 flex-1 h-full {{ request()->routeIs('peserta.presensi.index') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }} transition-colors duration-150">
                <i class="fas fa-calendar-check text-base"></i>
                <span class="text-xs font-medium">Presensi</span>
            </a>
            
            <!-- Riwayat -->
            <a href="{{ route('peserta.riwayat.index') }}" 
               class="flex flex-col items-center justify-center space-y-1 flex-1 h-full {{ request()->routeIs('peserta.riwayat.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }} transition-colors duration-150">
                <i class="fas fa-history text-base"></i>
                <span class="text-xs font-medium">Riwayat</span>
            </a>
            
            <!-- Izin/Sakit -->
            <a href="{{ route('peserta.izin.index') }}" 
               class="flex flex-col items-center justify-center space-y-1 flex-1 h-full {{ request()->routeIs('peserta.izin.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }} transition-colors duration-150">
                <i class="fas fa-file-medical text-base"></i>
                <span class="text-xs font-medium">Izin</span>
            </a>
            
            <!-- Kegiatan -->
            <a href="{{ route('peserta.kegiatan.index') }}" 
               class="flex flex-col items-center justify-center space-y-1 flex-1 h-full {{ request()->routeIs('peserta.kegiatan.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }} transition-colors duration-150">
                <i class="fas fa-tasks text-base"></i>
                <span class="text-xs font-medium">Kegiatan</span>
            </a>
        </div>
    </nav>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div id="successAlert" class="fixed top-20 left-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg z-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="closeAlert('successAlert')" class="text-green-500 hover:text-green-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div id="errorAlert" class="fixed top-20 left-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg z-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="closeAlert('errorAlert')" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 shadow-xl">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <span class="text-gray-700">Memproses...</span>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Base JavaScript -->
    <script>
        // Toggle Functions
        function toggleNotification() {
            const dropdown = document.getElementById('notificationDropdown');
            const userDropdown = document.getElementById('userDropdown');
            
            dropdown.classList.toggle('hidden');
            userDropdown.classList.add('hidden');
            
            // Load notifications when dropdown is opened
            if (!dropdown.classList.contains('hidden')) {
                loadNotifications();
            }
        }

        // Function to load notifications from server
        async function loadNotifications() {
            try {
                const response = await fetch('{{ route("notifications.recent") }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    renderNotifications(data.notifications);
                    updateNotificationBadge();
                } else {
                    console.error('Failed to load notifications');
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        }

        // Function to render notifications in the dropdown
        function renderNotifications(notifications) {
            const notificationList = document.getElementById('notificationList');
            
            if (notifications.length === 0) {
                notificationList.innerHTML = `
                    <div class="p-4 text-center text-gray-500 text-sm">
                        <i class="fas fa-bell-slash text-2xl mb-2"></i>
                        <p>Tidak ada notifikasi</p>
                    </div>
                `;
                return;
            }

            const notificationHtml = notifications.map(notification => {
                const indicatorColor = getNotificationColor(notification.type);
                const readClass = notification.is_read ? 'bg-gray-50' : 'bg-white';
                
                return `
                    <div class="p-4 hover:bg-gray-50 border-b border-gray-100 cursor-pointer ${readClass}" 
                         onclick="markNotificationAsRead(${notification.id})">
                        <div class="flex space-x-3">
                            <div class="w-2 h-2 ${indicatorColor} rounded-full mt-2 flex-shrink-0"></div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900">${notification.title}</p>
                                <p class="text-xs text-gray-500 mt-1">${notification.message}</p>
                                <p class="text-xs text-gray-400 mt-1">${notification.time_ago}</p>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            notificationList.innerHTML = notificationHtml;
        }

        // Function to get notification indicator color based on type
        function getNotificationColor(type) {
            switch (type) {
                case 'pengajuan_izin':
                    return 'bg-blue-500';
                case 'approval_izin':
                    return 'bg-green-500';
                case 'reminder_presensi':
                    return 'bg-yellow-500';
                case 'presensi_alert':
                    return 'bg-red-500';
                default:
                    return 'bg-gray-400';
            }
        }

        // Function to mark notification as read
        async function markNotificationAsRead(notificationId) {
            try {
                const response = await fetch(`{{ url('notifications') }}/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                if (response.ok) {
                    updateNotificationBadge();
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        }

        // Function to update notification badge count
        async function updateNotificationBadge() {
            try {
                const response = await fetch('{{ route("notifications.unread-count") }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    const badge = document.getElementById('notificationBadge');
                    
                    if (data.count > 0) {
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
            } catch (error) {
                console.error('Error updating notification badge:', error);
            }
        }

        // Function to mark all notifications as read
        async function markAllNotificationsAsRead() {
            try {
                const response = await fetch('{{ route("notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                if (response.ok) {
                    loadNotifications();
                    updateNotificationBadge();
                }
            } catch (error) {
                console.error('Error marking all notifications as read:', error);
            }
        }

        // Load notification count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateNotificationBadge();
        });

        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            const notificationDropdown = document.getElementById('notificationDropdown');
            
            dropdown.classList.toggle('hidden');
            notificationDropdown.classList.add('hidden');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const notificationBtn = event.target.closest('[onclick="toggleNotification()"]');
            const userBtn = event.target.closest('[onclick="toggleUserDropdown()"]');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const userDropdown = document.getElementById('userDropdown');

            if (!notificationBtn && !notificationDropdown.contains(event.target)) {
                notificationDropdown.classList.add('hidden');
            }

            if (!userBtn && !userDropdown.contains(event.target)) {
                userDropdown.classList.add('hidden');
            }
        });

        // Alert Functions
        function closeAlert(alertId) {
            document.getElementById(alertId).remove();
        }

        // Auto close alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('[id$="Alert"]');
            alerts.forEach(alert => alert.remove());
        }, 5000);

        // Loading Functions
        function showLoading() {
            document.getElementById('loadingOverlay').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.add('hidden');
        }

        // Geolocation Helper
        function getCurrentLocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Geolocation tidak didukung oleh browser ini'));
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    position => resolve(position),
                    error => reject(error),
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 300000
                    }
                );
            });
        }

        // Format Date Helper
        function formatDate(date) {
            return new Intl.DateTimeFormat('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }).format(date);
        }

        function formatTime(time) {
            return new Intl.DateTimeFormat('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            }).format(time);
        }
        
        // Check Font Awesome and force load if needed
        function checkAndFixFontAwesome() {
            console.log('Checking Font Awesome...');
            
            // Method 1: Check if FontAwesome CSS is loaded
            const stylesheets = Array.from(document.styleSheets);
            const faLoaded = stylesheets.some(sheet => {
                try {
                    return sheet.href && (sheet.href.includes('font-awesome') || sheet.href.includes('fontawesome'));
                } catch (e) {
                    return false;
                }
            });
            
            console.log('Font Awesome CSS loaded:', faLoaded);
            
            // Method 2: Test icon rendering
            const testElement = document.createElement('i');
            testElement.className = 'fas fa-home';
            testElement.style.position = 'absolute';
            testElement.style.left = '-9999px';
            testElement.style.fontSize = '16px';
            document.body.appendChild(testElement);
            
            setTimeout(() => {
                const computedStyle = window.getComputedStyle(testElement, ':before');
                const content = computedStyle.getPropertyValue('content');
                const fontFamily = computedStyle.getPropertyValue('font-family');
                
                console.log('Icon content:', content);
                console.log('Font family:', fontFamily);
                
                document.body.removeChild(testElement);
                
                // If Font Awesome is not working, apply manual fixes
                if (!faLoaded || content === 'none' || content === '' || content === 'normal' || !fontFamily.includes('Font Awesome')) {
                    console.warn('Font Awesome not working properly. Applying fixes...');
                    
                    // Force load Font Awesome
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
                    link.onload = () => console.log('Font Awesome force-loaded successfully');
                    link.onerror = () => console.error('Failed to force-load Font Awesome');
                    document.head.appendChild(link);
                } else {
                    console.log('Font Awesome loaded successfully');
                }
            }, 500);
        }
        
        // Check Font Awesome after page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for CSS to load
            setTimeout(checkAndFixFontAwesome, 1000);
        });
    </script>
    
    @stack('scripts')
</body>
</html>
