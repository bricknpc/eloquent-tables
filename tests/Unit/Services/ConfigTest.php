<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Services;

use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Config::class)]
class ConfigTest extends TestCase
{
    public function test_it_returns_the_correct_theme_when_set(): void
    {
        config()->set('eloquent-tables.theme', Theme::Bootstrap5);

        /** @var Config $config */
        $config = $this->app->make(Config::class);

        $this->assertSame(Theme::Bootstrap5, $config->theme());
    }

    public function test_it_returns_the_default_theme_when_none_set(): void
    {
        config()->set('eloquent-tables.theme', null);

        /** @var Config $config */
        $config = $this->app->make(Config::class);

        $this->assertSame(Theme::Bootstrap5, $config->theme());
    }

    public function test_it_returns_the_correct_search_query_name(): void
    {
        config()->set('eloquent-tables.search.query_name', 'q');

        /** @var Config $config */
        $config = $this->app->make(Config::class);

        $this->assertSame('q', $config->searchQueryName());
    }

    public function test_it_returns_the_correct_sort_query_name(): void
    {
        config()->set('eloquent-tables.sorting.query_name', 's');

        /** @var Config $config */
        $config = $this->app->make(Config::class);

        $this->assertSame('s', $config->sortQueryName());
    }
}
