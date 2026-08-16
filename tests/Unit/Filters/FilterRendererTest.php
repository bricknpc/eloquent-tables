<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Filters;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Filters\Filter;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\AccentStyle;
use BrickNPC\EloquentTables\Styles\StyleResolver;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\Filters\FilterRenderer;
use BrickNPC\EloquentTables\Services\TableParameters;
use BrickNPC\EloquentTables\Services\TablePreferences;
use BrickNPC\EloquentTables\Tests\Resources\TestTable;
use BrickNPC\EloquentTables\Styles\Contexts\TableContext;

/**
 * @internal
 */
#[CoversClass(FilterRenderer::class)]
#[UsesClass(Filter::class)]
#[UsesClass(Config::class)]
#[UsesClass(Table::class)]
#[UsesClass(TableParameters::class)]
#[UsesClass(TablePreferences::class)]
#[UsesClass(Theme::class)]
#[UsesClass(StyleResolver::class)]
#[UsesClass(AccentStyle::class)]
#[UsesClass(StyleSet::class)]
#[UsesClass(TableContext::class)]
class FilterRendererTest extends TestCase
{
    public function test_it_returns_the_correct_view(): void
    {
        /** @var FilterRenderer $builder */
        $builder = $this->app->make(FilterRenderer::class);

        $filter = new Filter('name', []);

        /** @var Request $request */
        $request = $this->app->make('request');

        $view = $builder->build($filter, new TestTable(), $request);

        $this->assertSame($filter->view(), $view->name());
    }

    public function test_it_renders_the_correct_theme(): void
    {
        config()->set('eloquent-tables.theme', Theme::Bootstrap5);

        /** @var FilterRenderer $builder */
        $builder = $this->app->make(FilterRenderer::class);

        $filter = new Filter('name', []);

        /** @var Request $request */
        $request = $this->app->make('request');

        $view = $builder->build($filter, new TestTable(), $request);

        $this->assertArrayHasKey('theme', $view->getData());
        $this->assertSame(Theme::Bootstrap5, $view->getData()['theme']);
    }

    public function test_it_renders_the_options(): void
    {
        /** @var FilterRenderer $builder */
        $builder = $this->app->make(FilterRenderer::class);

        $filter = new Filter('name', []);

        /** @var Request $request */
        $request = $this->app->make('request');

        $view = $builder->build($filter, new TestTable(), $request);

        $this->assertArrayHasKey('options', $view->getData());
        $this->assertIsArray($view->getData()['options']);
    }

    public function test_it_renders_the_name(): void
    {
        /** @var FilterRenderer $builder */
        $builder = $this->app->make(FilterRenderer::class);

        $filter = new Filter('name', []);

        /** @var Request $request */
        $request = $this->app->make('request');

        $view = $builder->build($filter, new TestTable(), $request);

        $this->assertArrayHasKey('name', $view->getData());
        $this->assertSame('name', $view->getData()['name']);
    }

    public function test_it_renders_the_query_name(): void
    {
        config()->set('eloquent-tables.filtering.query_name', 'test');

        /** @var FilterRenderer $builder */
        $builder = $this->app->make(FilterRenderer::class);

        $filter = new Filter('name', []);

        /** @var Request $request */
        $request = $this->app->make('request');

        $view = $builder->build($filter, new TestTable(), $request);

        $this->assertArrayHasKey('queryName', $view->getData());
        // The table is named "test" and the filtering query name is configured to "test", so the
        // view composes test[test][name] as the field name.
        $this->assertSame('test[test]', $view->getData()['queryName']);
    }

    public function test_it_renders_the_value(): void
    {
        /** @var FilterRenderer $builder */
        $builder = $this->app->make(FilterRenderer::class);

        $filter = new Filter('name', []);

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('test', ['filter' => ['name' => 'test-value']]);

        $view = $builder->build($filter, new TestTable(), $request);

        $this->assertArrayHasKey('value', $view->getData());
        $this->assertSame('test-value', $view->getData()['value']);
    }

    public function test_a_filter_form_keeps_its_sibling_filters(): void
    {
        // Covers AE7. A GET form replaces the query string, so without the hidden inputs changing one
        // filter would silently clear the others.
        /** @var FilterRenderer $builder */
        $builder = $this->app->make(FilterRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('test', [
            'filter' => ['active' => '1', 'team' => '7'],
            'search' => 'ada',
        ]);

        $html = $builder->build(new Filter('active', []), new TestTable(), $request)->render();

        $this->assertStringContainsString('<input type="hidden" name="test[filter][team]" value="7"/>', $html);
        $this->assertStringContainsString('<input type="hidden" name="test[search]" value="ada"/>', $html);
        // Its own key is the select, not a hidden input.
        $this->assertStringNotContainsString('type="hidden" name="test[filter][active]"', $html);
    }

    public function test_the_filter_view_renders_without_a_surrounding_table(): void
    {
        // Regression: the view uses $mainTableStyle but FilterRenderer never passed it, so any table
        // with a filter threw "Undefined variable $mainTableStyle" when rendered.
        /** @var FilterRenderer $builder */
        $builder = $this->app->make(FilterRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $html = $builder->build(new Filter('name', []), new TestTable(), $request)->render();

        $this->assertStringContainsString('<select name="test[filter][name]"', $html);
    }
}
