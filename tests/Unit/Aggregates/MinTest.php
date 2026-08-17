<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Aggregates;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Aggregates\Min;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Contracts\Aggregate;
use BrickNPC\EloquentTables\Concerns\AggregatesValues;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;

/**
 * @internal
 */
#[CoversClass(Min::class)]
#[UsesClass(AggregatesValues::class)]
class MinTest extends TestCase
{
    public function test_an_empty_page_has_no_rows_to_aggregate(): void
    {
        // Covers R13.
        $this->assertNull(new Min()->forPage(new Collection()));
    }

    public function test_an_empty_query_has_no_rows_to_aggregate(): void
    {
        // Covers R13, AE7.
        $this->assertNull(new Min()->forQuery(TestModel::query(), 'amount'));
    }

    public function test_it_aggregates_the_values_on_the_page(): void
    {
        $this->assertSame(10, new Min()->forPage(new Collection([10, 20, 30])));
    }

    public function test_it_aggregates_the_whole_query(): void
    {
        $this->rows(10, 20, 30);

        $this->assertSame(10, new Min()->forQuery(TestModel::query(), 'amount'));
    }

    public function test_it_ignores_null_values_on_the_page(): void
    {
        $this->assertSame(
            new Min()->forPage(new Collection([10, 20, 30])),
            new Min()->forPage(new Collection([10, null, 20, null, 30])),
        );
    }

    public function test_it_is_an_aggregate(): void
    {
        $this->assertInstanceOf(Aggregate::class, new Min());
    }

    public function test_it_declares_whether_it_carries_the_column_unit(): void
    {
        $this->assertTrue(new Min()->carriesColumnUnit());
    }

    private function rows(int ...$amounts): void
    {
        foreach ($amounts as $index => $amount) {
            $model         = new TestModel();
            $model->name   = 'Row ' . $index;
            $model->email  = 'row' . $index . '@example.com';
            $model->amount = $amount;
            $model->save();
        }
    }
}
