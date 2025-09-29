<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdministratorController;
use App\Http\Controllers\PesertaController;
use App\Http\Controllers\PembimbingController;
use App\Http\Controllers\JamKerjaController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\PerizinanController;

Route::get('/', [AuthController::class, 'showLoginForm']);

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Dashboard routes with role-based access
Route::middleware(['auth', 'role:admin,pembimbing'])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
});

// Admin kehadiran routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/kehadiran', [App\Http\Controllers\KehadiranController::class, 'index'])->name('kehadiran.index');
    Route::get('/admin/kehadiran', [App\Http\Controllers\KehadiranController::class, 'index'])->name('admin.kehadiran.index');
    Route::get('/kehadiran/create', [App\Http\Controllers\KehadiranController::class, 'create'])->name('kehadiran.create');
    Route::post('/kehadiran', [App\Http\Controllers\KehadiranController::class, 'store'])->name('kehadiran.store');
    Route::get('/kehadiran/export-excel', [App\Http\Controllers\KehadiranController::class, 'exportExcel'])->name('admin.kehadiran.export-excel');
    Route::get('/kehadiran/export-pdf', [App\Http\Controllers\KehadiranController::class, 'exportPdf'])->name('admin.kehadiran.export-pdf');
    Route::get('/kehadiran/export', [App\Http\Controllers\KehadiranController::class, 'export'])->name('kehadiran.export');
    Route::get('/kehadiran/{presensi}', [App\Http\Controllers\KehadiranController::class, 'show'])->name('kehadiran.show');
    Route::get('/admin/kehadiran/{presensi}', [App\Http\Controllers\KehadiranController::class, 'show'])->name('admin.kehadiran.show');
    Route::get('/kehadiran/{presensi}/edit', [App\Http\Controllers\KehadiranController::class, 'edit'])->name('kehadiran.edit');
    Route::put('/kehadiran/{presensi}', [App\Http\Controllers\KehadiranController::class, 'update'])->name('kehadiran.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route untuk mengakses file perizinan
Route::get('/storage/perizinan/{filename}', function ($filename) {
    $path = storage_path('app/public/perizinan/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->file($path);
})->name('perizinan.file');

// Admin routes with role protection
Route::middleware(['auth', 'role:admin,pembimbing'])->prefix('admin')->name('admin.')->group(function () {
    // Resource route untuk administrator
    Route::resource('administrator', AdministratorController::class);
    
    // Additional route untuk toggle status administrator
    Route::patch('/administrator/{administrator}/toggle-status', [AdministratorController::class, 'toggleStatus'])->name('administrator.toggle-status');

    // Resource route untuk peserta (moved to admin namespace)
    Route::resource('peserta', PesertaController::class)
        ->parameters(['peserta' => 'peserta']);
    
    // Export routes untuk peserta
    Route::get('/peserta/export/excel', [PesertaController::class, 'exportExcel'])->name('peserta.export-excel');
    Route::get('/peserta/export/pdf', [PesertaController::class, 'exportPdf'])->name('peserta.export-pdf');

    // Resource route untuk pembimbing
    Route::resource('pembimbing', PembimbingController::class);

    // Additional route untuk toggle status pembimbing
    Route::patch('/pembimbing/{pembimbing}/toggle-status', [PembimbingController::class, 'toggleStatus'])->name('pembimbing.toggle-status');

    // Resource route untuk jam kerja
    Route::resource('jam-kerja', JamKerjaController::class);

    // Additional route untuk toggle status jam kerja
    Route::patch('/jam-kerja/{jamKerja}/toggle-status', [JamKerjaController::class, 'toggleStatus'])->name('jam-kerja.toggle-status');

    // Resource route untuk lokasi
    Route::resource('lokasi', LokasiController::class);
    
    // Monitoring Kegiatan routes
    Route::prefix('monitoring-kegiatan')->name('monitoring-kegiatan.')->group(function () {
        Route::get('/', [App\Http\Controllers\MonitoringKegiatanController::class, 'index'])->name('index');
        Route::get('/export', [App\Http\Controllers\MonitoringKegiatanController::class, 'export'])->name('export');
        Route::get('/export-excel', [App\Http\Controllers\MonitoringKegiatanController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export-pdf', [App\Http\Controllers\MonitoringKegiatanController::class, 'exportPdf'])->name('export-pdf');
    });
    
    // Admin alias untuk monitoring kegiatan
    Route::prefix('admin/monitoring-kegiatan')->name('admin.monitoring-kegiatan.')->group(function () {
        Route::get('/', [App\Http\Controllers\MonitoringKegiatanController::class, 'index'])->name('index');
        Route::get('/export-excel', [App\Http\Controllers\MonitoringKegiatanController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export-pdf', [App\Http\Controllers\MonitoringKegiatanController::class, 'exportPdf'])->name('export-pdf');
    });
    
    // Routes untuk perizinan (pembimbing)
    Route::prefix('perizinan')->name('perizinan.')->group(function () {
        Route::get('/', [PerizinanController::class, 'pembimbingDashboard'])->name('dashboard');
        Route::post('/{perizinan}/approve', [PerizinanController::class, 'approve'])->name('approve');
        Route::post('/{perizinan}/reject', [PerizinanController::class, 'reject'])->name('reject');
        Route::get('/{perizinan}', [PerizinanController::class, 'show'])->name('show');
    });
    
    // Routes untuk pengajuan presensi (admin)
    Route::prefix('pengajuan-presensi')->name('pengajuan-presensi.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\PengajuanPresensiController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\PengajuanPresensiController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [App\Http\Controllers\Admin\PengajuanPresensiController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [App\Http\Controllers\Admin\PengajuanPresensiController::class, 'reject'])->name('reject');
    });
});

// Routes untuk Pembimbing
Route::middleware(['auth', 'role:pembimbing'])->prefix('pembimbing')->name('pembimbing.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Pembimbing\DashboardController::class, 'index'])->name('dashboard');
    
    // Peserta routes
    Route::prefix('peserta')->name('peserta.')->group(function () {
        Route::get('/', [App\Http\Controllers\Pembimbing\PesertaController::class, 'index'])->name('index');
        Route::get('/{peserta}', [App\Http\Controllers\Pembimbing\PesertaController::class, 'show'])->name('show');
        Route::get('/export/excel', [App\Http\Controllers\Pembimbing\PesertaController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [App\Http\Controllers\Pembimbing\PesertaController::class, 'exportPdf'])->name('export.pdf');
    });
    
    // Kehadiran routes
    Route::prefix('kehadiran')->name('kehadiran.')->group(function () {
        Route::get('/', [App\Http\Controllers\Pembimbing\KehadiranController::class, 'index'])->name('index');
        Route::get('/export/excel', [App\Http\Controllers\Pembimbing\KehadiranController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [App\Http\Controllers\Pembimbing\KehadiranController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/{presensi}', [App\Http\Controllers\Pembimbing\KehadiranController::class, 'show'])->name('show');
        Route::get('/{presensi}/detail', [App\Http\Controllers\Pembimbing\KehadiranController::class, 'getDetail'])->name('detail');
    });
    
    // Izin routes
    Route::prefix('izin')->name('izin.')->group(function () {
        Route::get('/', [App\Http\Controllers\Pembimbing\IzinController::class, 'index'])->name('index');
        Route::post('/{perizinan}/approve', [App\Http\Controllers\Pembimbing\IzinController::class, 'approve'])->name('approve');
        Route::post('/{perizinan}/reject', [App\Http\Controllers\Pembimbing\IzinController::class, 'reject'])->name('reject');
        Route::get('/{perizinan}', [App\Http\Controllers\Pembimbing\IzinController::class, 'show'])->name('show');
    });
    
    // Laporan Kegiatan routes
    Route::prefix('laporan-kegiatan')->name('laporan-kegiatan.')->group(function () {
        Route::get('/', [App\Http\Controllers\Pembimbing\LaporanKegiatanController::class, 'index'])->name('index');
        Route::get('/export/excel', [App\Http\Controllers\Pembimbing\LaporanKegiatanController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [App\Http\Controllers\Pembimbing\LaporanKegiatanController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/{peserta}', [App\Http\Controllers\Pembimbing\LaporanKegiatanController::class, 'show'])->name('show');
        Route::get('/{peserta}/export', [App\Http\Controllers\Pembimbing\LaporanKegiatanController::class, 'export'])->name('export');
    });

    // Profile routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [App\Http\Controllers\Pembimbing\ProfileController::class, 'index'])->name('index');
        Route::put('/update', [App\Http\Controllers\Pembimbing\ProfileController::class, 'updateProfile'])->name('update');
        Route::put('/update-password', [App\Http\Controllers\Pembimbing\ProfileController::class, 'updatePassword'])->name('update-password');
    });
});

// Routes untuk Peserta (Mobile-First Interface) - FIXED ROUTE CONFLICT
Route::middleware(['auth', 'role:peserta'])->prefix('peserta')->name('peserta.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Peserta\DashboardController::class, 'index'])->name('dashboard');
    
    // Presensi routes
    Route::prefix('presensi')->name('presensi.')->group(function () {
        Route::get('/', [App\Http\Controllers\Peserta\PresensiController::class, 'index'])->name('index');
        Route::post('/store', [App\Http\Controllers\Peserta\PresensiController::class, 'store'])->name('store');
        Route::post('/checkin', [App\Http\Controllers\Peserta\PresensiController::class, 'checkin'])->name('checkin');
        Route::post('/checkout', [App\Http\Controllers\Peserta\PresensiController::class, 'checkout'])->name('checkout');
    });
    
    // Check kegiatan harian route
    Route::get('/check-kegiatan-harian', [App\Http\Controllers\Peserta\PresensiController::class, 'checkKegiatanHarian'])->name('check-kegiatan-harian');
    
    // Riwayat routes
    Route::prefix('riwayat')->name('riwayat.')->group(function () {
        Route::get('/', [App\Http\Controllers\Peserta\RiwayatController::class, 'index'])->name('index');
        Route::get('/export-pdf', [App\Http\Controllers\Peserta\RiwayatController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{id}/detail', [App\Http\Controllers\Peserta\RiwayatController::class, 'detail'])->name('detail');
    });
    
    // Izin routes - Update untuk menggunakan PerizinanController
    Route::prefix('izin')->name('izin.')->group(function () {
        Route::get('/', [PerizinanController::class, 'index'])->name('index');
        Route::post('/store', [PerizinanController::class, 'store'])->name('store');
        Route::get('/{perizinan}', [PerizinanController::class, 'show'])->name('show');
        Route::get('/{perizinan}/edit', [PerizinanController::class, 'edit'])->name('edit');
        Route::put('/{perizinan}', [PerizinanController::class, 'update'])->name('update');
        Route::delete('/{perizinan}', [PerizinanController::class, 'destroy'])->name('destroy');
    });
    
    // Pengajuan Presensi routes
    Route::prefix('pengajuan-presensi')->name('pengajuan-presensi.')->group(function () {
        Route::post('/store', [App\Http\Controllers\Peserta\PengajuanPresensiController::class, 'store'])->name('store');
        Route::get('/data', [App\Http\Controllers\Peserta\PengajuanPresensiController::class, 'getData'])->name('data');
        Route::get('/{id}', [App\Http\Controllers\Peserta\PengajuanPresensiController::class, 'show'])->name('show');
        Route::delete('/{id}', [App\Http\Controllers\Peserta\PengajuanPresensiController::class, 'destroy'])->name('destroy');
        Route::get('/valid-dates', [App\Http\Controllers\Peserta\PengajuanPresensiController::class, 'getValidDates'])->name('valid-dates');
    });
    
    // Kegiatan routes
    Route::prefix('kegiatan')->name('kegiatan.')->group(function () {
        Route::get('/', [App\Http\Controllers\Peserta\KegiatanController::class, 'index'])->name('index');
        Route::get('/search', [App\Http\Controllers\Peserta\KegiatanController::class, 'search'])->name('search');
        Route::get('/export', [App\Http\Controllers\Peserta\KegiatanController::class, 'export'])->name('export');
        Route::get('/export/pdf', [App\Http\Controllers\Peserta\KegiatanController::class, 'exportPdf'])->name('export.pdf');
        Route::post('/store', [App\Http\Controllers\Peserta\KegiatanController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\Peserta\KegiatanController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\Peserta\KegiatanController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Peserta\KegiatanController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Peserta\KegiatanController::class, 'destroy'])->name('destroy');
    });

    // Profile routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [App\Http\Controllers\Peserta\ProfileController::class, 'index'])->name('index');
        Route::put('/update', [App\Http\Controllers\Peserta\ProfileController::class, 'updateProfile'])->name('update');
        Route::put('/update-password', [App\Http\Controllers\Peserta\ProfileController::class, 'updatePassword'])->name('update-password');
    });
});

// Notification routes (accessible by both pembimbing and peserta)
Route::middleware(['auth'])->group(function () {
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/recent', [App\Http\Controllers\NotificationController::class, 'getRecent'])->name('recent');
        Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::post('/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    });
});