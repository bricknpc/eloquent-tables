<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Providers;

use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use BrickNPC\EloquentTables\Table;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Translation\Translator;
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
            $table->setLogger($this->getLogger());
            $table->request = $this->getRequest();
            $table->trans   = $this->getTranslator();
            $table->builder = $this->getConcrete(TableViewBuilder::class);
        });

        $this->registerFormatters();
    }

    private function registerFormatters(): void
    {
        /** @var \DateTimeZone|string $timezone */
        $timezone = $this->getConfig('app.timezone', 'utc');

        if (!$timezone instanceof \DateTimeZone) {
            $timezone = new \DateTimeZone($timezone);
        }

        /** @var string $currency */
        $currency = $this->getConfig('app.currency', 'EUR');

        $this->app->bind(CurrencyFormatter::class, fn (Application $app) => new CurrencyFormatter(
            $app->getLocale(),
            $currency,
        ));

        $this->app->bind(DateFormatter::class, fn (Application $app) => new DateFormatter(
            $app->getLocale(),
            $timezone,
        ));

        $this->app->bind(DateTimeFormatter::class, fn (Application $app) => new DateTimeFormatter(
            $app->getLocale(),
            $timezone,
        ));

        /** @var int $decimals */
        $decimals = $this->getConfig('app.decimals', 2);

        $this->app->bind(NumberFormatter::class, fn (Application $app) => new NumberFormatter(
            $app->getLocale(),
            $decimals,
        ));
    }

    private function getConfig(string $key, mixed $default = null): mixed
    {
        /** @var Repository $config */
        $config = $this->app->make('config');

        return $config->get($key, $default);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $abstract
     *
     * @return T
     */
    private function getConcrete(string $abstract): object
    {
        /** @var T $concrete */
        $concrete = $this->app->make($abstract);

        return $concrete;
    }

    private function getLogger(): LoggerInterface
    {
        /** @var LoggerInterface $logger */
        $logger = $this->app->make('log');

        return $logger;
    }

    private function getTranslator(): Translator
    {
        /** @var Translator $translator */
        $translator = $this->app->make('translator');

        return $translator;
    }

    private function getRequest(): Request
    {
        /** @var Request $request */
        $request = $this->app->make('request');

        return $request;
    }
}
