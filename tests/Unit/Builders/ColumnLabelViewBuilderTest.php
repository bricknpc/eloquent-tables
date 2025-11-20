<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\Sort;
use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\ColumnType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use BrickNPC\EloquentTables\Builders\ColumnLabelViewBuilder;

/**
 * @internal
 */
#[CoversClass(ColumnLabelViewBuilder::class)]
#[UsesClass(Column::class)]
#[UsesClass(Config::class)]
#[UsesClass(ColumnType::class)]
class ColumnLabelViewBuilderTest extends TestCase
{
    public function test_it_returns_the_correct_view(): void
    {
        /** @var ColumnLabelViewBuilder $builder */
        $builder = $this->app->make(ColumnLabelViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $column  = new Column('name');

        $view = $builder->build($request, $column);

        $this->assertSame('eloquent-tables::table.th', $view->name());
    }

    #[DataProvider('sortOrderProvider')]
    public function test_it_builds_the_correct_sort_url(?Sort $currentOrder, ?Sort $nextOrder): void
    {
        /** @var ColumnLabelViewBuilder $builder */
        $builder = $this->app->make(ColumnLabelViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        if ($currentOrder !== null) {
            $request->query->set('sort', ['name' => $currentOrder->value]);
        }

        $column = new Column('name');

        $view = $builder->build($request, $column);

        $this->assertArrayHasKey('href', $view->getData());
        if ($nextOrder === null) {
            $this->assertSame('http://localhost/?', $view->getData()['href']);
        } else {
            $this->assertSame('http://localhost/?sort%5Bname%5D=' . $nextOrder->value, $view->getData()['href']);
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

    public function test_it_renders_the_correct_theme(): void
    {
        config()->set('eloquent-tables.theme', Theme::Bootstrap5);

        /** @var ColumnLabelViewBuilder $builder */
        $builder = $this->app->make(ColumnLabelViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $column  = new Column('name');

        $view = $builder->build($request, $column);

        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('theme', $view->getData());
        $this->assertSame(Theme::Bootstrap5, $view->getData()['theme']);
    }

    // @todo add tests for other view data

    public function test_it_renders_the_correct_type(): void
    {
        config()->set('eloquent-tables.theme', Theme::Bootstrap5);

        /** @var ColumnLabelViewBuilder $builder */
        $builder = $this->app->make(ColumnLabelViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $column  = new Column('name')->checkbox();

        $view = $builder->build($request, $column);

        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('type', $view->getData());
        $this->assertSame(ColumnType::Checkbox, $view->getData()['type']);
    }
}
