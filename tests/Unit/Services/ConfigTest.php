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

    public function test_it_returns_the_correct_filter_query_name(): void
    {
        config()->set('eloquent-tables.filtering.query_name', 'f');

        /** @var Config $config */
        $config = $this->app->make(Config::class);

        $this->assertSame('f', $config->filterQueryName());
    }

    public function test_it_returns_the_correct_page_query_name(): void
    {
        config()->set('eloquent-tables.pagination.page_query_name', 'p');

        /** @var Config $config */
        $config = $this->app->make(Config::class);

        $this->assertSame('p', $config->pageQueryName());
    }

    public function test_it_returns_the_default_page_query_name_when_none_set(): void
    {
        config()->set('eloquent-tables.pagination', []);

        /** @var Config $config */
        $config = $this->app->make(Config::class);

        $this->assertSame('page', $config->pageQueryName());
    }

    public function test_it_returns_the_correct_per_page_query_name(): void
    {
        config()->set('eloquent-tables.pagination.per_page_query_name', 'size');

        /** @var Config $config */
        $config = $this->app->make(Config::class);

        $this->assertSame('size', $config->perPageQueryName());
    }

    public function test_it_returns_the_default_per_page_query_name_when_none_set(): void
    {
        config()->set('eloquent-tables.pagination', []);

        /** @var Config $config */
        $config = $this->app->make(Config::class);

        $this->assertSame('per_page', $config->perPageQueryName());
    }

    public function test_it_returns_the_correct_preferences_cookie_name(): void
    {
        config()->set('eloquent-tables.preferences.cookie_name', 'prefs');

        /** @var Config $config */
        $config = $this->app->make(Config::class);

        $this->assertSame('prefs', $config->preferencesCookieName());
    }

    public function test_it_returns_the_default_preferences_cookie_name_when_none_set(): void
    {
        config()->set('eloquent-tables.preferences', []);

        /** @var Config $config */
        $config = $this->app->make(Config::class);

        $this->assertSame('eloquent_tables_preferences', $config->preferencesCookieName());
    }

    public function test_preferences_are_enabled_when_none_set(): void
    {
        config()->set('eloquent-tables.preferences', []);

        /** @var Config $config */
        $config = $this->app->make(Config::class);

        $this->assertTrue($config->preferencesEnabled());
    }

    public function test_preferences_can_be_disabled(): void
    {
        config()->set('eloquent-tables.preferences.enabled', false);

        /** @var Config $config */
        $config = $this->app->make(Config::class);

        $this->assertFalse($config->preferencesEnabled());
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

        yield [
            'checkIcon', 'check', new HtmlString('&check;'),
        ];

        yield [
            'crossIcon', 'cross', new HtmlString('&cross;'),
        ];
    }
}
