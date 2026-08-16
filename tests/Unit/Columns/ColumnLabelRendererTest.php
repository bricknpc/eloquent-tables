<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Columns;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\Sort;
use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\ColumnType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use BrickNPC\EloquentTables\Services\TableParameters;
use BrickNPC\EloquentTables\Services\TablePreferences;
use BrickNPC\EloquentTables\Tests\Resources\TestTable;
use BrickNPC\EloquentTables\Columns\ColumnLabelRenderer;

/**
 * @internal
 */
#[CoversClass(ColumnLabelRenderer::class)]
#[UsesClass(Column::class)]
#[UsesClass(Config::class)]
#[UsesClass(ColumnType::class)]
#[UsesClass(Table::class)]
#[UsesClass(TableParameters::class)]
#[UsesClass(TablePreferences::class)]
class ColumnLabelRendererTest extends TestCase
{
    public function test_it_returns_the_correct_view(): void
    {
        /** @var ColumnLabelRenderer $builder */
        $builder = $this->app->make(ColumnLabelRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $column  = new Column('name');

        $view = $builder->build($request, new TestTable(), $column);

        $this->assertSame('eloquent-tables::table.th', $view->name());
    }

    #[DataProvider('sortOrderProvider')]
    public function test_it_builds_the_correct_sort_url(?Sort $currentOrder, ?Sort $nextOrder): void
    {
        /** @var ColumnLabelRenderer $builder */
        $builder = $this->app->make(ColumnLabelRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        if ($currentOrder !== null) {
            $request->query->set('test', ['sort' => ['name' => $currentOrder->value]]);
        }

        $column = new Column('name');

        $view = $builder->build($request, new TestTable(), $column);

        $this->assertArrayHasKey('href', $view->getData());
        if ($nextOrder === null) {
            // Cycling the last column off leaves an explicit empty sort, so a stored sort does not
            // quietly reappear on the next visit.
            $this->assertSame('http://localhost/?test%5Bsort%5D=', $view->getData()['href']);
        } else {
            $this->assertSame('http://localhost/?test%5Bsort%5D%5Bname%5D=' . $nextOrder->value, $view->getData()['href']);
        }
    }

    public static function sortOrderProvider(): \Generator
    {
        yield [
            null,
            Sort::Asc,
        ];

        yield [
            Sort::Asc,
            Sort::Desc,
        ];

        yield [
            Sort::Desc,
            null,
        ];
    }

    public function test_the_sort_link_preserves_every_other_parameter(): void
    {
        /** @var ColumnLabelRenderer $builder */
        $builder = $this->app->make(ColumnLabelRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('test', ['search' => 'ada', 'filter' => ['active' => '1']]);
        $request->query->set('orders', ['sort' => ['total' => 'desc']]);

        $href = $builder->build($request, new TestTable(), new Column('name'))->getData()['href'];

        $this->assertStringContainsString('test%5Bsort%5D%5Bname%5D=asc', $href);
        $this->assertStringContainsString('test%5Bsearch%5D=ada', $href);
        $this->assertStringContainsString('test%5Bfilter%5D%5Bactive%5D=1', $href);
        $this->assertStringContainsString('orders%5Bsort%5D%5Btotal%5D=desc', $href);
    }

    public function test_it_renders_the_correct_theme(): void
    {
        config()->set('eloquent-tables.theme', Theme::Bootstrap5);

        /** @var ColumnLabelRenderer $builder */
        $builder = $this->app->make(ColumnLabelRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $column  = new Column('name');

        $view = $builder->build($request, new TestTable(), $column);

        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('theme', $view->getData());
        $this->assertSame(Theme::Bootstrap5, $view->getData()['theme']);
    }

    // @todo add tests for other view data

    public function test_it_renders_the_correct_type(): void
    {
        config()->set('eloquent-tables.theme', Theme::Bootstrap5);

        /** @var ColumnLabelRenderer $builder */
        $builder = $this->app->make(ColumnLabelRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $column  = new Column('name')->checkbox();

        $view = $builder->build($request, new TestTable(), $column);

        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('type', $view->getData());
        $this->assertSame(ColumnType::Checkbox, $view->getData()['type']);
    }
}
