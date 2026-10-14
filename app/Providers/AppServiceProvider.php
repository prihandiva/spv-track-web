<?php

namespace App\Providers;

use App\Models\Shipment;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->header('x-forwarded-proto') === 'https' || str_contains(request()->header('host', ''), 'ngrok') || request()->isSecure()) {
            URL::forceScheme('https');
        }

        // Bagikan jumlah shipment yang sedang dalam proses staging aktif (status = draft) secara dinamis
        View::composer('components.layout', function ($view) {
            $activeStagingCount = 0;
            try {
                if (Schema::hasTable('shipments')) {
                    $activeStagingCount = Shipment::where('status', 'draft')->count();
                }
            } catch (\Throwable $e) {
                // Fail-safe jika database belum siap
            }
            $view->with('activeStagingCount', $activeStagingCount);
        });
    }
}

