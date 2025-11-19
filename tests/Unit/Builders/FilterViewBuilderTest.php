<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Filters\Filter;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Builders\FilterViewBuilder;

/**
 * @internal
 */
#[CoversClass(FilterViewBuilder::class)]
#[UsesClass(Filter::class)]
#[UsesClass(Config::class)]
class FilterViewBuilderTest extends TestCase
{
    public function test_it_returns_the_correct_view(): void
    {
        /** @var FilterViewBuilder $builder */
        $builder = $this->app->make(FilterViewBuilder::class);

        $filter = new Filter('name', []);

        /** @var Request $request */
        $request = $this->app->make('request');

        $view = $builder->build($filter, $request);

        $this->assertSame($filter->view(), $view->name());
    }

    public function test_it_renders_the_correct_theme(): void
    {
        config()->set('eloquent-tables.theme', Theme::Bootstrap5);

        /** @var FilterViewBuilder $builder */
        $builder = $this->app->make(FilterViewBuilder::class);

        $filter = new Filter('name', []);

        /** @var Request $request */
        $request = $this->app->make('request');

        $view = $builder->build($filter, $request);

        $this->assertArrayHasKey('theme', $view->getData());
        $this->assertSame(Theme::Bootstrap5, $view->getData()['theme']);
    }

    public function test_it_renders_the_options(): void
    {
        /** @var FilterViewBuilder $builder */
        $builder = $this->app->make(FilterViewBuilder::class);

        $filter = new Filter('name', []);

        /** @var Request $request */
        $request = $this->app->make('request');

        $view = $builder->build($filter, $request);

        $this->assertArrayHasKey('options', $view->getData());
        $this->assertIsArray($view->getData()['options']);
    }

    public function test_it_renders_the_name(): void
    {
        /** @var FilterViewBuilder $builder */
        $builder = $this->app->make(FilterViewBuilder::class);

        $filter = new Filter('name', []);

        /** @var Request $request */
        $request = $this->app->make('request');

        $view = $builder->build($filter, $request);

        $this->assertArrayHasKey('name', $view->getData());
        $this->assertSame('name', $view->getData()['name']);
    }

    public function test_it_renders_the_query_name(): void
    {
        config()->set('eloquent-tables.filtering.query_name', 'test');

        /** @var FilterViewBuilder $builder */
        $builder = $this->app->make(FilterViewBuilder::class);

        $filter = new Filter('name', []);

        /** @var Request $request */
        $request = $this->app->make('request');

        $view = $builder->build($filter, $request);

        $this->assertArrayHasKey('queryName', $view->getData());
        $this->assertSame('test', $view->getData()['queryName']);
    }

    public function test_it_renders_the_value(): void
    {
        /** @var FilterViewBuilder $builder */
        $builder = $this->app->make(FilterViewBuilder::class);

        $filter = new Filter('name', []);

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('filter', ['name' => 'test-value']);

        $view = $builder->build($filter, $request);

        $this->assertArrayHasKey('value', $view->getData());
        $this->assertSame('test-value', $view->getData()['value']);
    }
}
