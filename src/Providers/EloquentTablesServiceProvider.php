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
    }

    public function register(): void {}
}
