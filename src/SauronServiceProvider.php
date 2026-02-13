<?php

declare(strict_types=1);

namespace FarmaciasDirect\Sauron;

use Illuminate\Support\ServiceProvider;

class SauronServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/sauron.php', 'sauron');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/sauron.php' => config_path('sauron.php'),
        ], 'sauron-config');
    }
}
