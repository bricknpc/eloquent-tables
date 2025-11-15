<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests;

use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as BaseTestCase;
use BrickNPC\EloquentTables\Providers\EloquentTablesServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // todo We don't need to do this for each test, but it's easier to do it here for now.
        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        // Mock the Blade Icons components
        Blade::component('blade-icon', '');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users');
        parent::tearDown();
    }

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
