<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class EloquentTablesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::addNamespace('eloquent-tables', __DIR__ . '/../../resources/views');

        $this->mergeConfigFrom(__DIR__ . '/../../config/eloquent-tables.php', 'eloquent-tables');

        $this->publishes([
            __DIR__ . '/../../config/eloquent-tables.php' => config_path('package.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/eloquent-tables'),
        ], 'views');
    }

    public function register(): void {}
}
