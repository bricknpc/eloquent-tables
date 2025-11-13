<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use BrickNPC\EloquentTables\Providers\EloquentTablesServiceProvider;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param Application $app
     *
     * @return array<int, class-string<ServiceProvider>>
     */
    protected function getPackageProviders($app): array
    {
        return [
            EloquentTablesServiceProvider::class,
        ];
    }
}
