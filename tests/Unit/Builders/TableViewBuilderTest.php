<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\TableStyle;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Attributes\Layout;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Builders\RowsBuilder;
use BrickNPC\EloquentTables\Services\LayoutFinder;
use BrickNPC\EloquentTables\Concerns\WithPagination;
use BrickNPC\EloquentTables\Builders\TableViewBuilder;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Builders\FilterViewBuilder;
use BrickNPC\EloquentTables\Factories\FormatterFactory;
use BrickNPC\EloquentTables\Builders\RowActionViewBuilder;
use BrickNPC\EloquentTables\Builders\MassActionViewBuilder;
use BrickNPC\EloquentTables\Builders\ColumnLabelViewBuilder;
use BrickNPC\EloquentTables\Builders\ColumnValueViewBuilder;
use BrickNPC\EloquentTables\Builders\TableActionViewBuilder;

/**
 * @internal
 */
#[CoversClass(TableViewBuilder::class)]
#[UsesClass(ColumnLabelViewBuilder::class)]
#[UsesClass(ColumnValueViewBuilder::class)]
#[UsesClass(TableActionViewBuilder::class)]
#[UsesClass(RowActionViewBuilder::class)]
#[UsesClass(FormatterFactory::class)]
#[UsesClass(LayoutFinder::class)]
#[UsesClass(Table::class)]
#[UsesClass(Column::class)]
#[UsesClass(TableStyle::class)]
#[UsesClass(Layout::class)]
#[UsesClass(WithPagination::class)]
#[UsesClass(Theme::class)]
#[UsesClass(Config::class)]
#[UsesClass(RowsBuilder::class)]
#[UsesClass(MassActionViewBuilder::class)]
#[UsesClass(FilterViewBuilder::class)]
class TableViewBuilderTest extends TestCase
{
    public function test_it_returns_the_correct_view(): void
    {
        /** @var TableViewBuilder $builder */
        $builder = $this->app->make(TableViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $table   = new class extends Table {
            public function columns(): array
            {
                return [];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        $view = $builder->build($table, $request);

        $this->assertSame('eloquent-tables::table', $view->name());
    }

    public function test_it_returns_the_correct_view_when_a_layout_is_specified_via_attribute(): void
    {
        /** @var TableViewBuilder $builder */
        $builder = $this->app->make(TableViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $table   = new #[Layout('app.layout')] class extends Table {
            public function columns(): array
            {
                return [];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        $view = $builder->build($table, $request);

        $this->assertSame('eloquent-tables::table-with-layout', $view->name());
    }

    public function test_it_returns_the_correct_view_when_a_layout_is_specified_via_method(): void
    {
        /** @var TableViewBuilder $builder */
        $builder = $this->app->make(TableViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $table   = new class extends Table {
            public function columns(): array
            {
                return [];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }

            public function layout(): Layout
            {
                return new Layout('app.layout');
            }
        };

        $view = $builder->build($table, $request);

        $this->assertSame('eloquent-tables::table-with-layout', $view->name());
    }

    public function test_it_builds_table_styles_correctly(): void
    {
        /** @var TableViewBuilder $builder */
        $builder = $this->app->make(TableViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $table = new class extends Table {
            public function columns(): array
            {
                return [];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        $view = $builder->build($table, $request);

        $this->assertArrayHasKey('tableStyles', $view->getData());
        $this->assertEmpty($view->getData()['tableStyles']);
    }

    public function test_it_gets_all_results_without_pagination(): void
    {
        /** @var TableViewBuilder $builder */
        $builder = $this->app->make(TableViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        // Create 100 test models
        for ($i = 0; $i < 100; ++$i) {
            DB::table('test_models')->insert([
                'name'       => sprintf('Test Model %d', $i),
                'email'      => sprintf('test-email-%d@test.com', $i),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $table = new class extends Table {
            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [];
            }
        };

        $view = $builder->build($table, $request);

        $viewData = $view->getData();

        $this->assertArrayHasKey('rows', $viewData);
        $this->assertInstanceOf(Collection::class, $viewData['rows']);
        $this->assertCount(100, $viewData['rows']);
        $this->assertNull($viewData['links']);
    }

    public function test_it_gets_paginated_results_with_pagination(): void
    {
        /** @var TableViewBuilder $builder */
        $builder = $this->app->make(TableViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        // Create 100 test models
        for ($i = 0; $i < 100; ++$i) {
            DB::table('test_models')->insert([
                'name'       => sprintf('Test Model %d', $i),
                'email'      => sprintf('test-email-%d@test.com', $i),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $table = new class extends Table {
            use WithPagination;

            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [];
            }
        };

        $view = $builder->build($table, $request);

        $viewData = $view->getData();

        $this->assertArrayHasKey('rows', $viewData);
        $this->assertInstanceOf(Collection::class, $viewData['rows']);
        $this->assertCount(15, $viewData['rows']);
        $this->assertNotNull($viewData['links']);
    }

    public function test_it_shows_search_form_when_there_are_searchable_columns(): void
    {
        /** @var TableViewBuilder $builder */
        $builder = $this->app->make(TableViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $table = new class extends Table {
            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [
                    new Column('name')->searchable(),
                ];
            }
        };

        $view = $builder->build($table, $request);

        $viewData = $view->getData();

        $this->assertArrayHasKey('showSearchForm', $viewData);
        $this->assertTrue($viewData['showSearchForm']);
        $this->assertArrayHasKey('tableSearchUrl', $viewData);
        $this->assertArrayHasKey('searchQuery', $viewData);
        $this->assertArrayHasKey('searchQueryName', $viewData);
    }
}
