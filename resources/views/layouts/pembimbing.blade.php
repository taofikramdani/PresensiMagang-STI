<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Pembimbing - Presensi Magang')</title>
    
    <!-- Vite Assets (Built CSS & JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Font Awesome - Multiple reliable sources -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Backup Font Awesome CDN -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.0.0/css/all.css">
    
    <!-- Google Fonts - Public Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Additional CSS -->
    @stack('styles')

    <style>
        * {
            font-family: 'Public Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        }
        
        body {
            font-family: 'Public Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        }

        /* Notification badge animation */
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        .notification-badge {
            animation: pulse 2s infinite;
        }

        /* Dropdown animation */
        .dropdown-enter {
            opacity: 0;
            transform: translateY(-10px);
        }

        .dropdown-enter-active {
            opacity: 1;
            transform: translateY(0);
            transition: all 0.2s ease-out;
        }

        /* Notification dropdown positioning fix */
        .notification-dropdown {
            position: absolute;
            right: 0;
            top: 100%;
            min-width: 320px;
            max-width: 90vw;
            max-height: 80vh;
        }

        @media (max-width: 640px) {
            .notification-dropdown {
                right: -100px;
                min-width: 280px;
                max-width: calc(100vw - 20px);
            }
        }

        /* Ensure dropdown doesn't get cut off */
        .notification-container {
            position: relative;
            z-index: 9999;
        }

        /* Lower z-index when mobile sidebar is open */
        body.sidebar-open .notification-container {
            z-index: 9997;
        }

        body.sidebar-open .notification-dropdown {
            z-index: 9997;
        }

        /* Custom scrollbar for notification dropdown */
        .notification-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .notification-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .notification-scroll::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .notification-scroll::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0040ff',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-100">
    <!-- Header -->
    <header class="w-full bg-white shadow-sm">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center space-x-6">
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-gray-500 hover:text-primary focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>

                <!-- Logo Section -->
                <div class="flex items-center space-x-4">
                    <a href="{{route('pembimbing.dashboard')}}" class="text-lg font-semibold text-blue-600">
                        <div class="flex items-center space-x-2">
                            <div class="h-8 w-8 text-blue grid place-items-center">
                                <i class="hidden md:block fa-solid fa-qrcode text-sm"></i>
                            </div>
                            <!-- Text hidden on mobile, shown on desktop -->
                            <span class="hidden md:block">Day-In</span>
                        </div>
                    </a>
                </div>

                <!-- Company Logos - hidden on mobile -->
                <div class="hidden md:flex items-center space-x-4 ml-8">
                    <img src="{{ asset('image/Danantara.png') }}" alt="Danantara Logo" class="h-6">
                    <img src="{{ asset('image/PLN.png') }}" alt="PLN Logo" class="h-6">
                </div>
            </div>

            <!-- Right side: Notifications and User info -->
            <div class="flex items-center space-x-4">
                <!-- Notification Icon -->
                <div class="relative notification-container">
                    <button id="notificationButton" class="relative p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-bell text-xl"></i>
                        <!-- Notification Badge -->
                        <span id="notificationBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold hidden notification-badge">
                            3
                        </span>
                    </button>

                    <!-- Notification Dropdown -->
                    <div id="notificationDropdown" class="notification-dropdown w-80 sm:w-96 bg-white rounded-lg shadow-xl border border-gray-200 hidden z-[9999] max-h-screen overflow-hidden">
                        <!-- Header -->
                        <div class="px-4 py-3 border-b border-gray-100 bg-white sticky top-0 z-10">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Notifikasi</h3>
                                <button id="markAllRead" class="text-sm text-blue-600 hover:text-blue-800 font-medium whitespace-nowrap">
                                    Tandai semua dibaca
                                </button>
                            </div>
                        </div>

                        <!-- Notification List -->
                        <div id="notificationList" class="max-h-80 overflow-y-auto notification-scroll">
                            <!-- Notifications will be loaded here via AJAX -->
                            <div class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-bell-slash text-3xl mb-2"></i>
                                <p>Memuat notifikasi...</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-4 py-3 border-t border-gray-100 bg-white sticky bottom-0">
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium block text-center">
                                Lihat semua notifikasi
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="relative">
                    <button id="userMenuButton" class="flex items-center space-x-2 focus:outline-none">
                        <div class="hidden sm:block text-right mr-3">
                            <div class="font-semibold text-gray-800">{{ Auth::user()->pembimbing->nama_lengkap ?? 'User' }}</div>
                            <div class="text-sm text-gray-500">Pembimbing</div>
                        </div>
                        <div
                            class="h-12 w-12 bg-gradient-to-r from-green-500 to-green-600 rounded-full text-white flex items-center justify-center font-semibold text-lg">
                            {{ strtoupper(substr(Auth::user()->pembimbing->nama_lengkap ?? Auth::user()->name ?? 'User', 0, 2)) }}
                        </div>
                        <i class="" id="userMenuArrow"></i>
                    </button>

                    <!-- Dropdown menu -->
                    <div id="userDropdown"
                        class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl py-1 hidden z-50 border border-gray-200">
                        
                        <!-- User Info Header -->
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="flex items-center space-x-3">
                                <div class="h-12 w-12 bg-gradient-to-r from-green-500 to-green-600 rounded-full text-white flex items-center justify-center font-semibold text-lg">
                                    {{ strtoupper(substr(Auth::user()->pembimbing->nama_lengkap ?? Auth::user()->name ?? 'User', 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 text-sm truncate">
                                        {{ Auth::user()->pembimbing->nama_lengkap ?? Auth::user()->name ?? 'User' }}
                                    </p>
                                    <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                                    <p class="text-xs text-green-600 font-medium">Pembimbing</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Menu Items -->
                        <div class="py-1">
                            <a href="{{ route('pembimbing.profile.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150">
                                <i class="fas fa-user-circle mr-3 text-gray-400 w-4"></i>
                                Profil Saya
                            </a>
                            
                            <hr class="my-1 border-gray-100">
                            
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <button type="submit" class="flex items-center w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150">
                                    <i class="fas fa-sign-out-alt mr-3 text-red-500 w-4"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation Menu - Hidden on mobile -->
    <nav class="hidden md:block bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8">
                <!-- Dashboard -->
                <a href="{{ route('pembimbing.dashboard') }}"
                    class="@if(request()->routeIs('pembimbing.dashboard')) border-primary text-primary @else text-gray-500 hover:text-gray-700 hover:border-gray-300 border-transparent @endif border-b-2 py-4 px-1 text-sm font-medium">
                    <i class="fas fa-tachometer-alt mr-2"></i>
                    Dashboard
                </a>

                <!-- Daftar Peserta -->
                <a href="{{ route('pembimbing.peserta.index') }}"
                    class="@if(request()->routeIs('pembimbing.peserta.*')) border-primary text-primary @else text-gray-500 hover:text-gray-700 hover:border-gray-300 border-transparent @endif border-b-2 py-4 px-1 text-sm font-medium">
                    <i class="fas fa-users mr-2"></i>
                    Daftar Peserta
                </a>

                <!-- Daftar Kehadiran -->
                <a href="{{ route('pembimbing.kehadiran.index') }}"
                    class="@if(request()->routeIs('pembimbing.kehadiran.*')) border-primary text-primary @else text-gray-500 hover:text-gray-700 hover:border-gray-300 border-transparent @endif border-b-2 py-4 px-1 text-sm font-medium">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    Daftar Kehadiran
                </a>

                <!-- Approval Izin/Sakit -->
                <a href="{{ route('pembimbing.izin.index') }}"
                    class="@if(request()->routeIs('pembimbing.izin.*')) border-primary text-primary @else text-gray-500 hover:text-gray-700 hover:border-gray-300 border-transparent @endif border-b-2 py-4 px-1 text-sm font-medium">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    Approval Izin/Sakit
                </a>

                <!-- Laporan Kegiatan -->
                <a href="{{ route('pembimbing.laporan-kegiatan.index') }}"
                    class="@if(request()->routeIs('pembimbing.laporan-kegiatan.*')) border-primary text-primary @else text-gray-500 hover:text-gray-700 hover:border-gray-300 border-transparent @endif border-b-2 py-4 px-1 text-sm font-medium">
                    <i class="fas fa-tasks mr-2"></i>
                    Laporan Kegiatan
                </a>
            </div>
        </div>
    </nav>

    <!-- Mobile Sidebar Overlay -->
    <div id="mobile-overlay" class="hidden fixed inset-0 z-[9998] md:hidden">
        <div class="fixed inset-0 bg-black opacity-50" id="sidebar-backdrop"></div>

        <!-- Mobile Sidebar -->
        <div id="mobile-sidebar"
            class="fixed inset-y-0 left-0 z-[9999] w-64 bg-white shadow-xl transform -translate-x-full transition-transform duration-300 ease-in-out">
            <div class="flex flex-col h-full">
                <!-- Sidebar Header -->
                <div
                    class="flex items-center justify-between px-4 py-4 bg-gradient-to-r from-green-500 to-green-400 text-white">
                    <div class="flex items-center space-x-2">
                        <div class="h-8 w-8 text-white grid place-items-center rounded">
                            <i class="fa-solid fa-qrcode text-sm"></i>
                        </div>
                        <span class="font-semibold">Pembimbing STI Jabar</span>
                    </div>
                    <button id="close-sidebar" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Company Logos -->
                <div class="px-4 py-3 bg-gray-50 border-b">
                    <div class="flex items-center space-x-3">
                        <img src="{{ asset('image/Danantara.png') }}" alt="Danantara Logo" class="h-6">
                        <img src="{{ asset('image/PLN.png') }}" alt="PLN Logo" class="h-6">
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="flex-1 overflow-y-auto py-4">
                    <nav class="px-4 space-y-2">
                        <a href="{{ route('pembimbing.dashboard') }}"
                            class="flex items-center px-3 py-2 text-sm font-medium @if(request()->routeIs('pembimbing.dashboard')) text-primary bg-blue-50 @else text-gray-700 hover:bg-gray-100 hover:text-primary @endif rounded-md">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            Dashboard
                        </a>

                        <!-- Pembimbingan Section -->
                        <div class="mt-4">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                Pembimbingan
                            </div>
                            <a href="{{ route('pembimbing.peserta.index') }}"
                                class="flex items-center px-3 py-2 mt-1 text-sm text-gray-700 rounded-md hover:bg-gray-100 hover:text-primary">
                                <i class="fas fa-users mr-3"></i>
                                Daftar Peserta
                            </a>
                            <a href="{{ route('pembimbing.kehadiran.index') }}"
                                class="flex items-center px-3 py-2 mt-1 text-sm text-gray-700 rounded-md hover:bg-gray-100 hover:text-primary">
                                <i class="fas fa-clipboard-check mr-3"></i>
                                Daftar Kehadiran
                            </a>
                            <a href="{{ route('pembimbing.izin.index') }}"
                                class="flex items-center px-3 py-2 mt-1 text-sm text-gray-700 rounded-md hover:bg-gray-100 hover:text-primary">
                                <i class="fas fa-clipboard-list mr-3"></i>
                                Approval Izin/Sakit
                            </a>
                        </div>

                        <div class="mt-4">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                Laporan
                            </div>
                            <a href="{{ route('pembimbing.laporan-kegiatan.index') }}"
                                class="flex items-center px-3 py-2 mt-1 text-sm text-gray-700 rounded-md hover:bg-gray-100 hover:text-primary">
                                <i class="fas fa-tasks mr-3"></i>
                                Laporan Kegiatan
                            </a>
                        </div>

                        <!-- Settings Section -->
                        <div class="mt-4">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                Pengaturan
                            </div>
                            <a href="{{ route('pembimbing.profile.index') }}"
                                class="flex items-center px-3 py-2 mt-1 text-sm text-gray-700 rounded-md hover:bg-gray-100 hover:text-primary">
                                <i class="fas fa-user mr-3"></i>
                                Profil
                            </a>
                        </div>
                    </nav>
                </div>

                <!-- Logout -->
                <div class="border-t border-gray-200 p-4">
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="flex items-center w-full text-left px-3 py-2 text-sm text-red-600 rounded-md hover:bg-red-50">
                            <i class="fas fa-sign-out-alt mr-3"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Additional Scripts -->
    @stack('scripts')

    <script>
        // User dropdown functionality
        const setupUserDropdown = () => {
            const userMenuButton = document.getElementById('userMenuButton');
            const userDropdown = document.getElementById('userDropdown');
            const userMenuArrow = document.getElementById('userMenuArrow');

            if (!userMenuButton || !userDropdown) return;

            const toggleDropdown = (e) => {
                e.stopPropagation();
                const isHidden = userDropdown.classList.contains('hidden');
                
                if (isHidden) {
                    userDropdown.classList.remove('hidden');
                    userMenuArrow.style.transform = 'rotate(180deg)';
                } else {
                    userDropdown.classList.add('hidden');
                    userMenuArrow.style.transform = 'rotate(0deg)';
                }
            };

            const closeDropdown = () => {
                userDropdown.classList.add('hidden');
                userMenuArrow.style.transform = 'rotate(0deg)';
            };

            userMenuButton.addEventListener('click', toggleDropdown);

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
                    closeDropdown();
                }
            });

            // Close dropdown on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeDropdown();
                }
            });
        };

        // Notification dropdown functionality
        const setupNotificationDropdown = () => {
            const notificationButton = document.getElementById('notificationButton');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationBadge = document.getElementById('notificationBadge');
            const markAllReadBtn = document.getElementById('markAllRead');

            if (!notificationButton || !notificationDropdown) return;

            const toggleNotificationDropdown = (e) => {
                e.stopPropagation();
                const isHidden = notificationDropdown.classList.contains('hidden');
                
                // Don't open notification if sidebar is open on mobile
                const overlay = document.getElementById('mobile-overlay');
                if (overlay && !overlay.classList.contains('hidden')) {
                    return; // Prevent opening notification when sidebar is open
                }
                
                // Close user dropdown if open
                const userDropdown = document.getElementById('userDropdown');
                if (userDropdown && !userDropdown.classList.contains('hidden')) {
                    userDropdown.classList.add('hidden');
                    const userMenuArrow = document.getElementById('userMenuArrow');
                    if (userMenuArrow) {
                        userMenuArrow.style.transform = 'rotate(0deg)';
                    }
                }
                
                if (isHidden) {
                    notificationDropdown.classList.remove('hidden');
                    adjustDropdownPosition();
                } else {
                    notificationDropdown.classList.add('hidden');
                }
            };

            const adjustDropdownPosition = () => {
                const rect = notificationDropdown.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;

                // Reset positioning
                notificationDropdown.style.right = '0';
                notificationDropdown.style.left = 'auto';
                notificationDropdown.style.transform = 'none';

                // Check if dropdown goes off screen horizontally
                if (rect.right > viewportWidth) {
                    const overflowX = rect.right - viewportWidth + 20; // 20px margin
                    notificationDropdown.style.right = `${overflowX}px`;
                }

                // Check if dropdown goes off screen on mobile
                if (viewportWidth <= 640) {
                    notificationDropdown.style.right = '-100px';
                    notificationDropdown.style.width = 'calc(100vw - 40px)';
                    notificationDropdown.style.maxWidth = '350px';
                }

                // Check if dropdown goes off screen vertically
                const newRect = notificationDropdown.getBoundingClientRect();
                if (newRect.bottom > viewportHeight) {
                    const maxHeight = viewportHeight - newRect.top - 20; // 20px margin
                    notificationDropdown.style.maxHeight = `${maxHeight}px`;
                }
            };

            const closeNotificationDropdown = () => {
                notificationDropdown.classList.add('hidden');
            };

            const markAllAsRead = () => {
                // Hide badge when all notifications are read
                if (notificationBadge) {
                    notificationBadge.classList.add('hidden');
                }
                
                // Remove unread indicators
                const unreadIndicators = notificationDropdown.querySelectorAll('.w-2.h-2');
                unreadIndicators.forEach(indicator => {
                    indicator.classList.remove('bg-blue-500', 'bg-green-500', 'bg-yellow-500');
                    indicator.classList.add('bg-gray-300');
                });

                // Here you can add API call to mark notifications as read
                console.log('Marking all notifications as read...');
            };

            notificationButton.addEventListener('click', toggleNotificationDropdown);

            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    markAllAsRead();
                });
            }

            // Close notification dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!notificationButton.contains(e.target) && !notificationDropdown.contains(e.target)) {
                    closeNotificationDropdown();
                }
            });

            // Close notification dropdown on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeNotificationDropdown();
                }
            });

            // Adjust position on window resize
            window.addEventListener('resize', () => {
                if (!notificationDropdown.classList.contains('hidden')) {
                    adjustDropdownPosition();
                }
            });

            // Show notification badge by default (you can control this from backend)
            if (notificationBadge) {
                notificationBadge.classList.remove('hidden');
            }

            // Load notifications on dropdown open
            notificationButton.addEventListener('click', loadNotifications);
        };

        // Function to load notifications from server
        const loadNotifications = async () => {
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
        };

        // Function to render notifications in the dropdown
        const renderNotifications = (notifications) => {
            const notificationList = document.getElementById('notificationList');
            
            if (notifications.length === 0) {
                notificationList.innerHTML = `
                    <div class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-bell-slash text-3xl mb-2"></i>
                        <p>Tidak ada notifikasi</p>
                    </div>
                `;
                return;
            }

            const notificationHtml = notifications.map(notification => {
                const indicatorColor = getNotificationColor(notification.type);
                const readClass = notification.is_read ? 'bg-gray-50' : 'bg-white';
                
                return `
                    <div class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100 cursor-pointer transition-colors duration-150 ${readClass}" 
                         onclick="markNotificationAsRead(${notification.id})">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-2 h-2 ${indicatorColor} rounded-full mt-2"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">${notification.title}</p>
                                <p class="text-sm text-gray-500">${notification.message}</p>
                                <p class="text-xs text-gray-400 mt-1">${notification.time_ago}</p>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            notificationList.innerHTML = notificationHtml;
        };

        // Function to get notification indicator color based on type
        const getNotificationColor = (type) => {
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
        };

        // Function to mark notification as read
        const markNotificationAsRead = async (notificationId) => {
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
        };

        // Function to update notification badge count
        const updateNotificationBadge = async () => {
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
                        badge.textContent = data.count;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
            } catch (error) {
                console.error('Error updating notification badge:', error);
            }
        };

        // Function to mark all notifications as read
        const markAllNotificationsAsRead = async () => {
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
        };

        // Update the existing markAllAsRead function
        const markAllAsRead = () => {
            markAllNotificationsAsRead();
        };

        // Mobile sidebar functionality
        const setupMobileSidebar = () => {
            const menuButton = document.getElementById('mobile-menu-button');
            const overlay = document.getElementById('mobile-overlay');
            const sidebar = document.getElementById('mobile-sidebar');
            const closeSidebar = document.getElementById('close-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');

            const openSidebar = () => {
                // Close notification dropdown if open
                const notificationDropdown = document.getElementById('notificationDropdown');
                const userDropdown = document.getElementById('userDropdown');
                
                if (notificationDropdown && !notificationDropdown.classList.contains('hidden')) {
                    notificationDropdown.classList.add('hidden');
                }
                
                if (userDropdown && !userDropdown.classList.contains('hidden')) {
                    userDropdown.classList.add('hidden');
                    const userMenuArrow = document.getElementById('userMenuArrow');
                    if (userMenuArrow) {
                        userMenuArrow.style.transform = 'rotate(0deg)';
                    }
                }

                // Add class to body for z-index control
                document.body.classList.add('sidebar-open');

                overlay.classList.remove('hidden');
                setTimeout(() => {
                    sidebar.classList.remove('-translate-x-full');
                }, 10);
                // Prevent body scroll
                document.body.style.overflow = 'hidden';
            };

            const closeSidebarFunc = () => {
                sidebar.classList.add('-translate-x-full');
                setTimeout(() => {
                    overlay.classList.add('hidden');
                    // Remove class from body
                    document.body.classList.remove('sidebar-open');
                    // Restore body scroll
                    document.body.style.overflow = '';
                }, 300);
            };

            if (menuButton) {
                menuButton.addEventListener('click', openSidebar);
            }

            if (closeSidebar) {
                closeSidebar.addEventListener('click', closeSidebarFunc);
            }

            if (backdrop) {
                backdrop.addEventListener('click', closeSidebarFunc);
            }

            // Close sidebar on window resize if screen becomes large
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 768) {
                    closeSidebarFunc();
                }
            });
        };

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            // Setup mobile sidebar
            setupMobileSidebar();

            // Setup user dropdown
            setupUserDropdown();

            // Setup notification dropdown
            setupNotificationDropdown();
        });
    </script>
</body>

</html>