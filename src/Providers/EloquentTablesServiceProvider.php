<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Providers;

use BrickNPC\EloquentTables\Table;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use BrickNPC\EloquentTables\Formatters\DateFormatter;
use BrickNPC\EloquentTables\Builders\TableViewBuilder;
use BrickNPC\EloquentTables\Formatters\NumberFormatter;
use BrickNPC\EloquentTables\Formatters\CurrencyFormatter;
use BrickNPC\EloquentTables\Formatters\DateTimeFormatter;

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
            $table->trans       = $app->make('translator');
            $table->builder     = $app->make(TableViewBuilder::class);
        });

        $this->registerFormatters();
    }

    private function registerFormatters(): void
    {
        $timezone = $this->getConfig('app.timezone', \DateTimeZone::UTC);

        if (!$timezone instanceof \DateTimeZone) {
            $timezone = new \DateTimeZone($timezone);
        }

        $this->app->bind(CurrencyFormatter::class, fn (Application $app) => new CurrencyFormatter(
            $app->getLocale(),
            $this->getConfig('app.currency', 'EUR'),
        ));

        $this->app->bind(DateFormatter::class, fn (Application $app) => new DateFormatter(
            $app->getLocale(),
            $timezone,
        ));

        $this->app->bind(DateTimeFormatter::class, fn (Application $app) => new DateTimeFormatter(
            $app->getLocale(),
            $timezone,
        ));

        $this->app->bind(NumberFormatter::class, fn (Application $app) => new NumberFormatter(
            $app->getLocale(),
            $this->getConfig('app.decimals', 2),
        ));
    }

    private function getConfig(string $key, mixed $default = null): mixed
    {
        /** @var Repository $config */
        $config = $this->app->make('config');

        return $config->get($key, $default);
    }
}
