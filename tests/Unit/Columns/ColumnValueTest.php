<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Columns;

use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Columns\ColumnValue;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;

/**
 * @internal
 */
#[CoversClass(ColumnValue::class)]
#[UsesClass(Column::class)]
class ColumnValueTest extends TestCase
{
    public function test_it_reads_the_model_attribute_of_the_same_name(): void
    {
        $model       = new TestModel();
        $model->name = 'Ada';

        $this->assertSame('Ada', new ColumnValue()->resolve(new Column('name'), $model));
    }

    public function test_it_resolves_a_value_using_closure_instead(): void
    {
        $model       = new TestModel();
        $model->name = 'Ada';

        $column = new Column('name')->valueUsing(fn (TestModel $model) => strtoupper((string) $model->name));

        $this->assertSame('ADA', new ColumnValue()->resolve($column, $model));
    }

    public function test_the_closure_receives_the_model(): void
    {
        $model       = new TestModel();
        $model->name = 'Ada';
        $received    = null;

        $column = new Column('name')->valueUsing(function (TestModel $given) use (&$received) {
            $received = $given;

            return 'value';
        });

        new ColumnValue()->resolve($column, $model);

        $this->assertSame($model, $received);
    }

    public function test_a_missing_attribute_resolves_to_null(): void
    {
        $this->assertNull(new ColumnValue()->resolve(new Column('does_not_exist'), new TestModel()));
    }

    public function test_a_null_attribute_resolves_to_null(): void
    {
        $model         = new TestModel();
        $model->amount = null;

        $this->assertNull(new ColumnValue()->resolve(new Column('amount'), $model));
    }

    public function test_it_resolves_a_value_of_any_type(): void
    {
        $model         = new TestModel();
        $model->amount = 42;

        $this->assertSame(42, new ColumnValue()->resolve(new Column('amount'), $model));
    }
}
