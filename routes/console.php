<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('photos:clean-temp')->hourly();

// =========================================================================
// JADWAL AUTOMATED BACKUP & DISASTER RECOVERY (SPV-TRACK)
// =========================================================================
// 1. Backup Database Harian setiap tengah malam (00:00 WIB)
Schedule::command('spv:backup --only-db')->dailyAt('00:00');

// 2. Backup Penuh (Database + Seluruh Foto Evidence) setiap Minggu jam 01:00 WIB
Schedule::command('spv:backup')->weeklyOn(0, '01:00');

