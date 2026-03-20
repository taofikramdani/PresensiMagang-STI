<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Peserta;
use Carbon\Carbon;

class RefreshPesertaStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'peserta:refresh-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh status peserta berdasarkan tanggal periode magang';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai refresh status peserta...');
        
        $today = Carbon::now()->startOfDay();
        $updated = 0;
        
        // Ambil semua peserta yang memiliki tanggal_selesai
        $peserta = Peserta::whereNotNull('tanggal_selesai')->get();
        
        foreach ($peserta as $p) {
            $tanggalSelesai = Carbon::parse($p->tanggal_selesai)->startOfDay();
            $statusLama = $p->status;
            $statusBaru = null;
            
            if ($tanggalSelesai->isBefore($today)) {
                // Periode sudah berakhir -> non-aktif
                $statusBaru = 'non-aktif';
            } elseif ($tanggalSelesai->isAfter($today) || $tanggalSelesai->isToday()) {
                // Periode masih berlaku -> aktif
                $statusBaru = 'aktif';
            }
            
            // Update jika status berubah
            if ($statusBaru && $statusLama !== $statusBaru) {
                $p->updateQuietly(['status' => $statusBaru]);
                $this->line("✓ {$p->nama_lengkap}: {$statusLama} → {$statusBaru}");
                $updated++;
            }
        }
        
        $this->info("Selesai! {$updated} peserta diupdate statusnya.");
        
        return Command::SUCCESS;
    }
}
