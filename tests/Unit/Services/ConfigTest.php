<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Services;

use Illuminate\Support\HtmlString;
use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

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

    public function test_it_returns_the_correct_data_namespace_when_set(): void
    {
        config()->set('eloquent-tables.data-namespace', 'namespace');

        /** @var Config $config */
        $config = $this->app->make(Config::class);

        $this->assertSame('namespace', $config->dataNamespace());
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

    #[DataProvider('iconProvider')]
    public function test_it_returns_the_correct_icons(string $method, string $name, string|\Stringable $value): void
    {
        config()->set('eloquent-tables.icons.' . $name, $value);

        /** @var Config $config */
        $config = $this->app->make(Config::class);

        $icon = call_user_func([$config, $method]);

        $this->assertSame($value, $icon);
    }

    public static function iconProvider(): \Generator
    {
        yield [
            'searchIcon', 'search', new HtmlString('&#x1F50E;&#xFE0E;'),
        ];

        yield [
            'sortAscIcon', 'sort-asc', new HtmlString('&#x25B2;'),
        ];

        yield [
            'sortDescIcon', 'sort-desc', new HtmlString('&#x25BC;'),
        ];

        yield [
            'sortNoneIcon', 'sort-none', new HtmlString('&#x25C0;'),
        ];
    }
}
