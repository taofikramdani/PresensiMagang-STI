<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard | Day-In')</title>
    
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

        /* Enhanced menu hover effects */
        .nav-link {
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
            transition: left 0.6s;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        /* Smooth border animation */
        .border-b-2 {
            transition: all 0.3s ease-in-out;
        }

        /* Dropdown animation improvements */
        .group:hover .group-hover\\:block {
            animation: slideDown 0.2s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
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
                    <a href="{{route('dashboard')}}" class="text-lg font-semibold text-blue-600">
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

                <!-- User Menu -->
                <div class="relative">
                    <button id="userMenuButton" class="flex items-center space-x-2 focus:outline-none">
                        <div class="hidden sm:block text-right mr-3">
                            <div class="font-semibold text-gray-800">{{ Auth::user()->name ?? 'User' }}</div>
                            <div class="text-sm text-gray-500">{{ Auth::user()->getRoleDisplayName() ?? 'User' }}</div>
                        </div>
                        <div
                            class="h-12 w-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full text-white flex items-center justify-center font-semibold text-lg">
                            {{ strtoupper(substr(Auth::user()->name ?? 'User', 0, 2)) }}
                        </div>
                        <i class="" id="userMenuArrow"></i>
                    </button>

                    <!-- Dropdown menu -->
                    <div id="userDropdown"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden z-50 border border-gray-200">
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                                <i class="fas fa-sign-out-alt mr-2 text-red-500"></i>
                                Logout
                            </button>
                        </form>
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
                <a href="{{ route('dashboard') }}"
                    class="@if(request()->routeIs('dashboard')) border-primary text-primary @else text-gray-500 hover:text-gray-700 hover:border-gray-300 border-transparent @endif border-b-2 py-4 px-1 text-sm font-medium transition-colors duration-150">
                    <i class="fas fa-tachometer-alt mr-2"></i>
                    Dashboard
                </a>

                <!-- Data Master Dropdown -->
                <div class="relative group">
                    <button
                        class="@if(request()->routeIs('peserta.*') || request()->routeIs('pembimbing.*')) text-primary border-primary @else text-gray-500 hover:text-gray-700 hover:border-gray-300 border-transparent @endif border-b-2 py-4 px-1 text-sm font-medium flex items-center transition-colors duration-150">
                        <i class="fas fa-database mr-2"></i>
                        Data Master
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div
                        class="absolute left-0 mt-0 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block z-50">
                        <a href="{{ route('admin.administrator.index') }}"
                            class="@if(request()->routeIs('admin.administrator.*')) bg-blue-50 text-primary @else text-gray-700 hover:bg-gray-100 hover:text-primary @endif block px-4 py-2 text-sm transition-colors duration-150">
                            <i class="fas fa-user-shield mr-2"></i>
                            Data Administrator
                        </a>
                        <a href="{{ route('admin.peserta.index') }}"
                            class="@if(request()->routeIs('admin.peserta.*')) bg-blue-50 text-primary @else text-gray-700 hover:bg-gray-100 hover:text-primary @endif block px-4 py-2 text-sm transition-colors duration-150">
                            <i class="fas fa-users mr-2"></i>
                            Data Peserta
                        </a>
                        <a href="{{ route('admin.pembimbing.index') }}"
                            class="@if(request()->routeIs('admin.pembimbing.*')) bg-blue-50 text-primary @else text-gray-700 hover:bg-gray-100 hover:text-primary @endif block px-4 py-2 text-sm transition-colors duration-150">
                            <i class="fas fa-user-tie mr-2"></i>
                            Data Pembimbing
                        </a>
                    </div>
                </div>

                <!-- Kehadiran -->
                <a href="{{ route('kehadiran.index') }}"
                    class="@if(request()->routeIs('kehadiran.*')) border-primary text-primary @else text-gray-500 hover:text-gray-700 hover:border-gray-300 border-transparent @endif border-b-2 py-4 px-1 text-sm font-medium transition-colors duration-150">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    Kehadiran
                </a>

                <!-- Pengajuan Presensi -->
                <a href="{{ route('admin.pengajuan-presensi.index') }}"
                    class="@if(request()->routeIs('admin.pengajuan-presensi.*')) border-primary text-primary @else text-gray-500 hover:text-gray-700 hover:border-gray-300 border-transparent @endif border-b-2 py-4 px-1 text-sm font-medium transition-colors duration-150">
                    <i class="fas fa-clock mr-2"></i>
                    Pengajuan Presensi
                </a>

                <!-- Monitoring Kegiatan -->
                <a href="{{ route('admin.monitoring-kegiatan.index') }}"
                    class="@if(request()->routeIs('admin.monitoring-kegiatan.*')) border-primary text-primary @else text-gray-500 hover:text-gray-700 hover:border-gray-300 border-transparent @endif border-b-2 py-4 px-1 text-sm font-medium transition-colors duration-150">
                    <i class="fas fa-chart-line mr-2"></i>
                    Monitoring Kegiatan
                </a>

                <div class="relative group">
                    <button
                        class="@if(request()->routeIs('admin.lokasi.*') || request()->routeIs('admin.jam-kerja.*')) text-primary border-primary @else text-gray-500 hover:text-gray-700 hover:border-gray-300 border-transparent @endif border-b-2 py-4 px-1 text-sm font-medium flex items-center transition-colors duration-150">
                        <i class="fas fa-user-cog mr-2"></i>
                        Setting
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div
                        class="absolute left-0 mt-0 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block z-50">
                        <a href="{{ route('admin.lokasi.index') }}" class="@if(request()->routeIs('admin.lokasi.*')) bg-blue-50 text-primary @else text-gray-700 hover:bg-gray-100 hover:text-primary @endif block px-4 py-2 text-sm transition-colors duration-150">
                            <i class="fa-solid fa-location-dot mr-2"></i>
                            Lokasi
                        </a>
                        <a href="{{ route('admin.jam-kerja.index') }}"
                            class="@if(request()->routeIs('admin.jam-kerja.*')) bg-blue-50 text-primary @else text-gray-700 hover:bg-gray-100 hover:text-primary @endif block px-4 py-2 text-sm transition-colors duration-150">
                            <i class="fa-solid fa-clock mr-2"></i>
                            Jam Kerja
                        </a>
                    </div>
                </div>
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
                    class="flex items-center justify-between px-4 py-4 bg-gradient-to-r from-blue-500 to-yellow-300 text-white">
                    <div class="flex items-center space-x-2">
                        <div class="h-8 w-8 text-white grid place-items-center rounded">
                            <i class="fa-solid fa-qrcode text-sm"></i>
                        </div>
                        <span class="font-semibold">Day-In</span>
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
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center px-3 py-2 text-sm font-medium @if(request()->routeIs('dashboard')) text-primary bg-blue-50 @else text-gray-700 hover:bg-gray-100 hover:text-primary @endif rounded-md transition-colors duration-150">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            Dashboard
                        </a>

                        <!-- Data Master Section -->
                        <div class="mt-4">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                Data Master
                            </div>
                            <a href="{{ route('admin.administrator.index') }}"
                                class="flex items-center px-3 py-2 mt-1 text-sm @if(request()->routeIs('admin.administrator.*')) text-primary bg-blue-50 @else text-gray-700 hover:bg-gray-100 hover:text-primary @endif rounded-md transition-colors duration-150">
                                <i class="fas fa-user-shield mr-3"></i>
                                Data Administrator
                            </a>
                            <a href="{{ route('admin.peserta.index') }}"
                                class="flex items-center px-3 py-2 mt-1 text-sm @if(request()->routeIs('admin.peserta.*')) text-primary bg-blue-50 @else text-gray-700 hover:bg-gray-100 hover:text-primary @endif rounded-md transition-colors duration-150">
                                <i class="fas fa-users mr-3"></i>
                                Data Peserta
                            </a>
                            <a href="{{ route('admin.pembimbing.index') }}"
                                class="flex items-center px-3 py-2 mt-1 text-sm @if(request()->routeIs('admin.pembimbing.*')) text-primary bg-blue-50 @else text-gray-700 hover:bg-gray-100 hover:text-primary @endif rounded-md transition-colors duration-150">
                                <i class="fas fa-user-tie mr-3"></i>
                                Data Pembimbing
                            </a>
                        </div>

                        <div class="mt-4">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                Presensi & Monitoring
                            </div>
                            <a href="{{ route('kehadiran.index') }}"
                                class="flex items-center px-3 py-2 mt-1 text-sm @if(request()->routeIs('kehadiran.*')) text-primary bg-blue-50 @else text-gray-700 hover:bg-gray-100 hover:text-primary @endif rounded-md transition-colors duration-150">
                                <i class="fas fa-clipboard-check mr-3"></i>
                                Kehadiran
                            </a>
                            <a href="{{ route('admin.pengajuan-presensi.index') }}"
                                class="flex items-center px-3 py-2 mt-1 text-sm @if(request()->routeIs('admin.pengajuan-presensi.*')) text-primary bg-blue-50 @else text-gray-700 hover:bg-gray-100 hover:text-primary @endif rounded-md transition-colors duration-150">
                                <i class="fas fa-clock mr-3"></i>
                                Pengajuan Presensi
                            </a>
                            <a href="{{ route('admin.monitoring-kegiatan.index') }}"
                                class="flex items-center px-3 py-2 mt-1 text-sm @if(request()->routeIs('admin.monitoring-kegiatan.*')) text-primary bg-blue-50 @else text-gray-700 hover:bg-gray-100 hover:text-primary @endif rounded-md transition-colors duration-150">
                                <i class="fa-solid fa-chart-line mr-3"></i>
                                Monitoring Kegiatan
                            </a>
                        </div>

                        <!-- Settings Section -->
                        <div class="mt-4">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                Pengaturan
                            </div>
                            <a href="{{ route('admin.lokasi.index') }}"
                                class="flex items-center px-3 py-2 mt-1 text-sm @if(request()->routeIs('admin.lokasi.*')) text-primary bg-blue-50 @else text-gray-700 hover:bg-gray-100 hover:text-primary @endif rounded-md transition-colors duration-150">
                                <i class="fa-solid fa-location-dot mr-3"></i>
                                Lokasi
                            </a>
                            <a href="{{ route('admin.jam-kerja.index') }}"
                                class="flex items-center px-3 py-2 mt-1 text-sm @if(request()->routeIs('admin.jam-kerja.*')) text-primary bg-blue-50 @else text-gray-700 hover:bg-gray-100 hover:text-primary @endif rounded-md transition-colors duration-150">
                                <i class="fa-solid fa-clock mr-3"></i>
                                Jam Kerja
                            </a>
                        </div>
                    </nav>
                </div>

                <!-- Logout -->
                <div class="border-t border-gray-200 p-4">
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="flex items-center w-full text-left px-3 py-2 text-sm text-red-600 rounded-md hover:bg-red-50 transition-colors duration-150">
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

            // Handle Laravel flash messages with SweetAlert
            @if(session('success'))
                Swal.fire({
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            @endif

            @if(session('warning'))
                Swal.fire({
                    title: 'Peringatan!',
                    text: '{{ session('warning') }}',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            @endif
        });
    </script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Test SweetAlert -->
    <script>
        // Test if SweetAlert is loaded
        if (typeof Swal !== 'undefined') {
            console.log('SweetAlert2 loaded successfully');
        } else {
            console.error('SweetAlert2 failed to load');
        }
    </script>

    <!-- Additional Scripts -->
    @stack('scripts')
</body>

</html>