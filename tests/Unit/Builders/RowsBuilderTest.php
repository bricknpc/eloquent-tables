<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Builders;

use Mockery\Mock;
use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\Sort;
use BrickNPC\EloquentTables\Filters\Filter;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Builders\RowsBuilder;
use BrickNPC\EloquentTables\Concerns\WithPagination;
use BrickNPC\EloquentTables\Services\TableParameters;
use BrickNPC\EloquentTables\Services\RouteModelBinder;
use BrickNPC\EloquentTables\Services\TablePreferences;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;

/**
 * @internal
 */
#[CoversClass(RowsBuilder::class)]
#[UsesClass(Table::class)]
#[UsesClass(Config::class)]
#[UsesClass(TableParameters::class)]
#[UsesClass(TablePreferences::class)]
#[UsesClass(Column::class)]
#[UsesClass(WithPagination::class)]
#[UsesClass(Filter::class)]
#[UsesClass(RouteModelBinder::class)]
class RowsBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seedTestModel();
    }

    public function test_it_applies_search_when_there_are_searchable_columns(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = new class extends Table {
            public function columns(): array
            {
                return [
                    new Column('name')->searchable(),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['search' => 'this will not give any results']);

        $rows = $builder->build($table, $request);

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertCount(0, $rows);
    }

    public function test_it_wont_apply_search_when_search_parameter_is_empty(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = new class extends Table {
            public function columns(): array
            {
                return [
                    new Column('name')->searchable(),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['search' => '']);

        $rows = $builder->build($table, $request);

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertCount(50, $rows);
    }

    public function test_it_wont_apply_search_to_column_that_is_not_defined_as_searchable(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = new class extends Table {
            public function columns(): array
            {
                return [
                    new Column('name')->searchable(),
                    new Column('email'),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['search' => 'test-email-1@test.com']);

        $rows = $builder->build($table, $request);

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertCount(0, $rows);
    }

    public function test_it_searches_with_or_operator_for_different_columns(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = new class extends Table {
            public function columns(): array
            {
                return [
                    new Column('name')->searchable(),
                    new Column('email')->searchable(),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['search' => 'test-email-01@test.com']);

        $rows = $builder->build($table, $request);

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertCount(1, $rows);
    }

    public function test_if_invalid_sort_value_is_given_it_defaults_to_empty(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = new class extends Table {
            public function columns(): array
            {
                return [
                    new Column('name')->sortable(),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['sort' => 'invalid']);

        $rows = $builder->build($table, $request);

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertCount(50, $rows);
        $this->assertSame('Test Model 00', $rows[0]->name);
    }

    public function test_it_sorts_by_default_when_a_default_sort_order_is_set(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = new class extends Table {
            public function columns(): array
            {
                return [
                    new Column('name')->sortable(default: Sort::Desc),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');

        $rows = $builder->build($table, $request);

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertCount(50, $rows);
        $this->assertSame('Test Model 49', $rows[0]->name);
    }

    public function test_it_sorts_by_default_when_a_custom_default_sort_order_is_set(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = new class extends Table {
            public function columns(): array
            {
                return [
                    new Column('name')->sortable(default: fn (Request $request, Builder $query) => $query->orderBy('id', 'asc')),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');

        $rows = $builder->build($table, $request);

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertCount(50, $rows);
        $this->assertSame('Test Model 00', $rows[0]->name);
    }

    public function test_it_does_not_sort_by_default_when_there_is_a_sort_value_given_and_there_is_a_sort_value(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = new class extends Table {
            public function columns(): array
            {
                return [
                    new Column('name')->sortable(default: Sort::Desc),
                    new Column('email')->sortable(),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['sort' => ['email' => 'asc']]);

        $rows = $builder->build($table, $request);

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertCount(50, $rows);
        $this->assertSame('Test Model 00', $rows[0]->name);
    }

    public function test_it_sorts_by_column_when_there_is_a_sort_value_given_and_there_is_a_sort_value(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = new class extends Table {
            public function columns(): array
            {
                return [
                    new Column('name')->sortable(),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['sort' => ['name' => 'desc']]);

        $rows = $builder->build($table, $request);

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertCount(50, $rows);
        $this->assertSame('Test Model 49', $rows[0]->name);
    }

    public function test_it_sorts_by_column_when_there_is_a_sort_value_given_and_there_is_a_custom_sort_value(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = new class extends Table {
            public function columns(): array
            {
                return [
                    new Column('name')->sortable(sortUsing: fn (Request $request, Builder $query, Sort $direction) => $query->orderBy('id', $direction->value)),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['sort' => ['name' => 'asc']]);

        $rows = $builder->build($table, $request);

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertCount(50, $rows);
        $this->assertSame('Test Model 00', $rows[0]->name);
    }

    public function test_it_applies_filters(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = new class extends Table {
            public function columns(): array
            {
                return [
                    new Column('name')->sortable(),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }

            public function filters(): array
            {
                return [
                    new Filter('name', []),
                ];
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['filter' => ['name' => 'Test Model 01']]);

        $rows = $builder->build($table, $request);

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertCount(1, $rows);
        $this->assertSame('Test Model 01', $rows[0]->name);
    }

    public function test_it_applies_a_filter_under_the_configured_filtering_query_name(): void
    {
        // Regression: applyFilters read a hardcoded "filter" key, so a configured filtering query
        // name was honoured when the filter rendered but ignored when it applied.
        config()->set('eloquent-tables.filtering.query_name', 'refine');

        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = new class extends Table {
            public function columns(): array
            {
                return [
                    new Column('name'),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }

            public function filters(): array
            {
                return [
                    new Filter('name', []),
                ];
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['refine' => ['name' => 'Test Model 01']]);

        $rows = $builder->build($table, $request);

        $this->assertCount(1, $rows);
        $this->assertSame('Test Model 01', $rows[0]->name);
    }

    public function test_two_tables_on_one_request_sort_independently(): void
    {
        $makeTable = fn (string $name) => new class($name) extends Table {
            public function __construct(private readonly string $tableName) {}

            public function name(): string
            {
                return $this->tableName;
            }

            public function columns(): array
            {
                return [
                    new Column('name')->sortable(),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('users', ['sort' => ['name' => 'desc']]);
        $request->query->set('orders', ['sort' => ['name' => 'asc']]);

        /** @var RowsBuilder $usersBuilder */
        $usersBuilder = $this->app->make(RowsBuilder::class);

        /** @var RowsBuilder $ordersBuilder */
        $ordersBuilder = $this->app->make(RowsBuilder::class);

        $users  = $usersBuilder->build($makeTable('users'), $request);
        $orders = $ordersBuilder->build($makeTable('orders'), $request);

        $this->assertSame('Test Model 49', $users->first()->name);
        $this->assertSame('Test Model 00', $orders->first()->name);
    }

    public function test_the_paginator_uses_the_tables_nested_page_key(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = $this->paginatedTable('users');

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('users', ['sort' => ['name' => 'asc'], 'search' => 'Test']);
        $request->query->set('orders', ['page' => '2']);

        $paginator = $builder->build($table, $request);

        $url = $paginator->url(2);

        $this->assertStringContainsString('users%5Bpage%5D=2', $url);
        $this->assertStringContainsString('users%5Bsort%5D%5Bname%5D=asc', $url);
        $this->assertStringContainsString('users%5Bsearch%5D=Test', $url);
        $this->assertStringContainsString('orders%5Bpage%5D=2', $url);
    }

    public function test_each_table_paginates_independently(): void
    {
        /** @var RowsBuilder $usersBuilder */
        $usersBuilder = $this->app->make(RowsBuilder::class);

        /** @var RowsBuilder $ordersBuilder */
        $ordersBuilder = $this->app->make(RowsBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('users', ['page' => '2']);

        $users  = $usersBuilder->build($this->paginatedTable('users'), $request);
        $orders = $ordersBuilder->build($this->paginatedTable('orders'), $request);

        $this->assertSame(2, $users->currentPage());
        $this->assertSame(1, $orders->currentPage());
    }

    public function test_the_per_page_value_comes_from_the_tables_own_parameter(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('users', ['per_page' => '5']);
        $request->query->set('orders', ['per_page' => '25']);

        $paginator = $builder->build($this->paginatedTable('users'), $request);

        $this->assertSame(5, $paginator->perPage());
    }

    public function test_sort_precedence_follows_the_click_order_not_the_column_order(): void
    {
        $applied = [];

        $table = $this->recordingSortTable($applied);

        /** @var Request $request */
        $request = $this->app->make('request');
        // Clicked email first, then name — the reverse of the declaration order below.
        $request->query->set('table', ['sort' => ['email' => 'asc', 'name' => 'desc']]);

        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);
        $builder->build($table, $request);

        $this->assertSame(['email:asc', 'name:desc'], $applied);
    }

    public function test_sort_precedence_preserves_a_three_column_click_order(): void
    {
        $applied = [];

        $table = $this->recordingSortTable($applied);

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['sort' => ['created_at' => 'desc', 'name' => 'asc', 'email' => 'asc']]);

        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);
        $builder->build($table, $request);

        $this->assertSame(['created_at:desc', 'name:asc', 'email:asc'], $applied);
    }

    public function test_a_sort_key_for_a_column_that_is_not_sortable_is_ignored(): void
    {
        $applied = [];

        $table = $this->recordingSortTable($applied);

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['sort' => ['not_sortable' => 'asc', 'name' => 'asc']]);

        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);
        $builder->build($table, $request);

        $this->assertSame(['name:asc'], $applied);
    }

    public function test_a_sort_key_for_an_unknown_column_is_ignored(): void
    {
        $applied = [];

        $table = $this->recordingSortTable($applied);

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['sort' => ['nope' => 'asc', 'name' => 'asc']]);

        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);
        $builder->build($table, $request);

        $this->assertSame(['name:asc'], $applied);
    }

    public function test_an_unusable_sort_direction_is_ignored(): void
    {
        $applied = [];

        $table = $this->recordingSortTable($applied);

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['sort' => ['name' => 'sideways', 'email' => 'asc']]);

        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);
        $builder->build($table, $request);

        $this->assertSame(['email:asc'], $applied);
    }

    public function test_empty_filter_is_not_applied(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = new class extends Table {
            public function columns(): array
            {
                return [
                    new Column('name')->sortable(),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }

            public function filters(): array
            {
                return [
                    new Filter('name', []),
                ];
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['filter' => ['name' => '']]);

        $rows = $builder->build($table, $request);

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertCount(50, $rows);
    }

    public function test_invalid_filter_is_not_applied(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = new class extends Table {
            public function columns(): array
            {
                return [
                    new Column('name')->sortable(),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }

            public function filters(): array
            {
                return [
                    new Filter('name', []),
                ];
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['filter' => 'invalid']);

        $rows = $builder->build($table, $request);

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertCount(50, $rows);
    }

    public function test_it_returns_paginator_for_tables_with_pagination(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = new class extends Table {
            use WithPagination;

            public function columns(): array
            {
                return [
                    new Column('name')->sortable(),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');

        $rows = $builder->build($table, $request);

        $this->assertInstanceOf(LengthAwarePaginator::class, $rows);
        $this->assertCount(15, $rows);
    }

    public function test_it_caches_results_internally(): void
    {
        /** @var RowsBuilder $builder */
        $builder = $this->app->make(RowsBuilder::class);

        $table = new class extends Table {
            public function columns(): array
            {
                return [
                    new Column('name')->sortable(),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', ['sort' => ['name' => 'desc']]);

        $rows = $builder->build($table, $request);

        /** @var Mock|Table $table2 */
        $table2 = $this->mock(Table::class);
        $table2->shouldReceive('columns')->never();
        $table2->shouldReceive('query')->never();

        $rows2 = $builder->build($table2, $request);

        $this->assertSameSize($rows, $rows2);
        $this->assertSame($rows[0]->name, $rows2[0]->name);
        $this->assertSame($rows[49]->name, $rows2[49]->name);
    }

    private function seedTestModel(): void
    {
        for ($i = 0; $i < 50; ++$i) {
            DB::table('test_models')->insert([
                'name'       => 'Test Model ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'email'      => 'test-email-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT) . '@test.com',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function paginatedTable(string $name): Table
    {
        return new class($name) extends Table {
            use WithPagination;

            public function __construct(private readonly string $tableName) {}

            public function name(): string
            {
                return $this->tableName;
            }

            public function columns(): array
            {
                return [
                    new Column('name')->sortable()->searchable(),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };
    }

    /**
     * A table whose sortable columns record the order their sort is applied, so precedence is
     * observable directly rather than inferred from row output. Declaration order here is
     * name, email, created_at.
     *
     * @param list<string> $applied
     */
    private function recordingSortTable(array &$applied): Table
    {
        $record = function (string $name) use (&$applied) {
            return function ($request, $query, Sort $sort) use ($name, &$applied) {
                $applied[] = $name . ':' . $sort->value;
            };
        };

        return new class($record) extends Table {
            /** @param \Closure(string): \Closure $record */
            public function __construct(private readonly \Closure $record) {}

            public function columns(): array
            {
                return [
                    new Column('name')->sortable(($this->record)('name')),
                    new Column('email')->sortable(($this->record)('email')),
                    new Column('created_at')->sortable(($this->record)('created_at')),
                    new Column('not_sortable'),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };
    }
}
