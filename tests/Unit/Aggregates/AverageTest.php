<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Aggregates;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Aggregates\Average;
use BrickNPC\EloquentTables\Contracts\Aggregate;
use BrickNPC\EloquentTables\Concerns\AggregatesValues;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;

/**
 * @internal
 */
#[CoversClass(Average::class)]
#[UsesClass(AggregatesValues::class)]
class AverageTest extends TestCase
{
    public function test_an_empty_page_has_no_rows_to_aggregate(): void
    {
        // Covers R13.
        $this->assertNull(new Average()->forPage(new Collection()));
    }

    public function test_an_empty_query_has_no_rows_to_aggregate(): void
    {
        // Covers R13, AE7.
        $this->assertNull(new Average()->forQuery(TestModel::query(), 'amount'));
    }

    public function test_it_aggregates_the_values_on_the_page(): void
    {
        $this->assertSame(20.0, new Average()->forPage(new Collection([10, 20, 30])));
    }

    public function test_it_aggregates_the_whole_query(): void
    {
        $this->rows(10, 20, 30);

        $this->assertSame(20.0, new Average()->forQuery(TestModel::query(), 'amount'));
    }

    public function test_both_scopes_agree_on_type(): void
    {
        $this->rows(10, 20, 30);

        $this->assertSame(
            new Average()->forPage(new Collection([10, 20, 30])),
            new Average()->forQuery(TestModel::query(), 'amount'),
        );
    }

    public function test_it_ignores_null_values_on_the_page(): void
    {
        $this->assertSame(
            new Average()->forPage(new Collection([10, 20, 30])),
            new Average()->forPage(new Collection([10, null, 20, null, 30])),
        );
    }

    public function test_it_is_an_aggregate(): void
    {
        $this->assertInstanceOf(Aggregate::class, new Average());
    }

    public function test_it_declares_whether_it_carries_the_column_unit(): void
    {
        $this->assertTrue(new Average()->carriesColumnUnit());
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
