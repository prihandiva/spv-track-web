<?php

namespace App\Console\Commands;

use App\Services\PhotoProcessingService;
use Illuminate\Console\Command;

class CleanTemporaryPhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'photos:clean-temp {--hours=24 : Hapus file temp yang lebih lama dari X jam}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan folder upload foto sementara yang tidak disubmit petugas';

    /**
     * Execute the console command.
     */
    public function handle(PhotoProcessingService $photoService): int
    {
        $hours = (int) $this->option('hours');
        $this->info("Memeriksa dan membersihkan file foto sementara yang berusia > {$hours} jam...");

        $cleanedCount = $photoService->cleanExpiredTempUploads($hours);

        $this->info("Pembersihan selesai: {$cleanedCount} folder upload sementara berhasil dihapus.");

        return Command::SUCCESS;
    }
}
