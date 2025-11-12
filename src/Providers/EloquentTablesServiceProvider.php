<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Providers;

use BrickNPC\EloquentTables\Table;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class EloquentTablesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::addNamespace('eloquent-tables', __DIR__ . '/../../resources/views');

        $this->mergeConfigFrom(__DIR__ . '/../../config/eloquent-tables.php', 'eloquent-tables');
        $this->loadJsonTranslationsFrom(__DIR__ . '/../../resources/lang');

        $this->publishes([
            __DIR__ . '/../../config/eloquent-tables.php' => $this->app->configPath('package.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../resources/views' => $this->app->resourcePath('views/vendor/eloquent-tables'),
        ], 'views');

        $this->publishes([
            __DIR__ . '/../../resources/lang' => $this->app->langPath('vendor/eloquent-tables'),
        ], 'lang');
    }

    public function register(): void
    {
        $this->app->resolving(Table::class, function (Table $table, Application $app) {
            $table->setLogger($app->make('log'));
            $table->request     = $app->make('request');
            $table->viewFactory = $app->make('view');
            $table->trans       = $app->make('translator');
        });
    }
}
