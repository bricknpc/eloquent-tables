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
use BrickNPC\EloquentTables\Services\RouteModelBinder;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;

/**
 * @internal
 */
#[CoversClass(RowsBuilder::class)]
#[UsesClass(Table::class)]
#[UsesClass(Config::class)]
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
        $request->query->set('search', 'this will not give any results');

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
        $request->query->set('search', '');

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
        $request->query->set('search', 'test-email-1@test.com');

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
        $request->query->set('search', 'test-email-01@test.com');

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
        $request->query->set('sort', 'invalid');

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
        $request->query->set('sort', ['name' => 'desc']);

        $rows = $builder->build($table, $request);

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertCount(50, $rows);
        $this->assertSame('Test Model 49', $rows[0]->name);
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
        $request->query->set('filter', ['name' => 'Test Model 01']);

        $rows = $builder->build($table, $request);

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertCount(1, $rows);
        $this->assertSame('Test Model 01', $rows[0]->name);
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
        $request->query->set('filter', ['name' => '']);

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
        $request->query->set('filter', 'invalid');

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
        $request->query->set('sort', ['name' => 'desc']);

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
}
