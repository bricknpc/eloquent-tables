<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Styles\Contexts;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\TableRegion;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Styles\Contexts\RowContext;
use BrickNPC\EloquentTables\Styles\Contexts\CellContext;
use BrickNPC\EloquentTables\Styles\Contexts\TableContext;

/**
 * @internal
 */
#[CoversClass(TableContext::class)]
#[CoversClass(RowContext::class)]
#[CoversClass(CellContext::class)]
#[UsesClass(Column::class)]
class ContextsTest extends TestCase
{
    public function test_the_table_context_carries_the_request(): void
    {
        $request = $this->request();

        $this->assertSame($request, new TableContext($request)->request);
    }

    public function test_the_row_context_carries_the_model(): void
    {
        $model = new TestModel();

        $this->assertSame($model, new RowContext($this->request(), $model)->model);
    }

    public function test_the_cell_context_carries_the_column_and_the_model(): void
    {
        $column = new Column('name');
        $model  = new TestModel();

        $context = new CellContext($this->request(), $column, $model);

        $this->assertSame($column, $context->column);
        $this->assertSame($model, $context->model);
    }

    public function test_a_cell_context_defaults_to_the_body(): void
    {
        $this->assertSame(
            TableRegion::Body,
            new CellContext($this->request(), new Column('name'), new TestModel())->region,
        );
    }

    public function test_a_header_cell_context_carries_no_model(): void
    {
        $context = new CellContext($this->request(), new Column('name'), null, TableRegion::Header);

        $this->assertNull($context->model);
        $this->assertSame(TableRegion::Header, $context->region);
    }

    private function request(): Request
    {
        /** @var Request $request */
        $request = $this->app->make('request');

        return $request;
    }
}
