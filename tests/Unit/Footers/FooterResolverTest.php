<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Footers;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Aggregates\Max;
use BrickNPC\EloquentTables\Aggregates\Sum;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Aggregates\Count;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Aggregates\Median;
use BrickNPC\EloquentTables\Aggregates\Average;
use BrickNPC\EloquentTables\Columns\ColumnValue;
use BrickNPC\EloquentTables\Enums\AggregateScope;
use BrickNPC\EloquentTables\Styles\StyleResolver;
use BrickNPC\EloquentTables\Footers\FooterResolver;
use BrickNPC\EloquentTables\ValueObjects\FooterRow;
use BrickNPC\EloquentTables\Concerns\AggregatesValues;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Factories\FormatterFactory;
use BrickNPC\EloquentTables\Tests\Resources\DoubledSum;
use BrickNPC\EloquentTables\ValueObjects\ResolvedFooter;
use BrickNPC\EloquentTables\Formatters\CurrencyFormatter;
use BrickNPC\EloquentTables\ValueObjects\ResolvedFooterRow;

/**
 * @internal
 */
#[CoversClass(FooterResolver::class)]
#[UsesClass(Column::class)]
#[UsesClass(ColumnValue::class)]
#[UsesClass(FooterRow::class)]
#[UsesClass(ResolvedFooter::class)]
#[UsesClass(ResolvedFooterRow::class)]
#[UsesClass(AggregateScope::class)]
#[UsesClass(Sum::class)]
#[UsesClass(Average::class)]
#[UsesClass(Median::class)]
#[UsesClass(Count::class)]
#[UsesClass(Max::class)]
#[UsesClass(AggregatesValues::class)]
#[UsesClass(CurrencyFormatter::class)]
#[UsesClass(FormatterFactory::class)]
#[UsesClass(DoubledSum::class)]
#[UsesClass(StyleResolver::class)]
#[UsesClass(Config::class)]
class FooterResolverTest extends TestCase
{
    public function test_a_page_row_and_a_total_row_produce_different_values(): void
    {
        // Covers AE1.
        $this->seedRows(10, 20, 30, 40, 50);

        $columns = [new Column('amount')->aggregate(new Sum())];

        $footer = $this->resolver()->resolve(
            [
                new FooterRow(new Sum(), AggregateScope::Page, 'This page'),
                new FooterRow(new Sum(), AggregateScope::Total, 'All rows'),
            ],
            $columns,
            $this->page(10, 20),
            TestModel::query(),
        );

        $this->assertSame('30', $footer->rows[0]->values[0]);
        $this->assertSame('150', $footer->rows[1]->values[0]);
        $this->assertSame('This page', $footer->rows[0]->label);
        $this->assertSame('All rows', $footer->rows[1]->label);
    }

    public function test_a_column_that_did_not_opt_in_stays_empty(): void
    {
        // Covers AE2.
        $columns = [new Column('invoice_number'), new Column('amount')->aggregate(new Sum())];

        $footer = $this->resolve($columns, new FooterRow(new Sum(), AggregateScope::Page, 'Total'));

        $this->assertSame('', $footer->rows[0]->values[0]);
        $this->assertSame('30', $footer->rows[0]->values[1]);
    }

    public function test_an_aggregate_with_no_portable_total_stays_empty(): void
    {
        // Covers AE3.
        $this->seedRows(10, 20, 30);

        $columns = [new Column('amount')->aggregate(new Median())];

        $page = $this->resolver()->resolve(
            [new FooterRow(new Median(), AggregateScope::Page, 'Median')],
            $columns,
            $this->page(10, 20, 30),
            TestModel::query(),
        );

        $total = $this->resolver()->resolve(
            [new FooterRow(new Median(), AggregateScope::Total, 'Median')],
            $columns,
            $this->page(10, 20, 30),
            TestModel::query(),
        );

        $this->assertSame('20', $page->rows[0]->values[0]);
        $this->assertSame('', $total->rows[0]->values[0]);
    }

    public function test_a_computed_column_has_a_page_value_and_no_total(): void
    {
        // Covers AE4.
        $this->seedRows(10, 20, 30);

        $columns = [
            new Column('line_total', valueUsing: static fn (TestModel $model) => ((int) $model->amount) * 2)
                ->aggregate(new Sum()),
        ];

        $page = $this->resolver()->resolve(
            [new FooterRow(new Sum(), AggregateScope::Page, 'Page')],
            $columns,
            $this->page(10, 20, 30),
            TestModel::query(),
        );

        $total = $this->resolver()->resolve(
            [new FooterRow(new Sum(), AggregateScope::Total, 'Total')],
            $columns,
            $this->page(10, 20, 30),
            TestModel::query(),
        );

        $this->assertSame('120', $page->rows[0]->values[0]);
        $this->assertSame('', $total->rows[0]->values[0]);
    }

    public function test_a_unit_carrying_aggregate_uses_the_column_formatter_and_a_count_does_not(): void
    {
        // Covers AE5.
        $columns = [
            new Column('amount')
                ->currency(locale: 'en_US', currency: 'USD')
                ->aggregate(new Sum(), new Count()),
        ];

        $footer = $this->resolve(
            $columns,
            new FooterRow(new Sum(), AggregateScope::Page, 'Sum'),
            new FooterRow(new Count(), AggregateScope::Page, 'Count'),
        );

        $this->assertStringContainsString('30', $footer->rows[0]->values[0]);
        $this->assertStringContainsString('$', $footer->rows[0]->values[0]);
        $this->assertSame('2', $footer->rows[1]->values[0]);
    }

    public function test_every_row_label_spans_the_same_columns(): void
    {
        // Covers AE6.
        $columns = [
            new Column('name'),
            new Column('region'),
            new Column('amount')->aggregate(new Sum(), new Median()),
            new Column('quantity')->aggregate(new Sum()),
        ];

        $footer = $this->resolve(
            $columns,
            new FooterRow(new Sum(), AggregateScope::Page, 'Sum'),
            new FooterRow(new Median(), AggregateScope::Page, 'Median'),
        );

        $this->assertSame(2, $footer->labelSpan);
        $this->assertSame('', $footer->rows[1]->values[3]);
    }

    public function test_an_empty_result_set_zeroes_only_the_aggregates_that_have_a_zero(): void
    {
        // Covers AE7.
        $columns = [new Column('amount')->aggregate(new Sum(), new Average(), new Count())];

        $footer = $this->resolver()->resolve(
            [
                new FooterRow(new Sum(), AggregateScope::Page, 'Sum'),
                new FooterRow(new Average(), AggregateScope::Page, 'Average'),
                new FooterRow(new Count(), AggregateScope::Page, 'Count'),
            ],
            $columns,
            new Collection(),
            TestModel::query(),
        );

        $this->assertSame('0', $footer->rows[0]->values[0]);
        $this->assertSame('', $footer->rows[1]->values[0]);
        $this->assertSame('0', $footer->rows[2]->values[0]);
    }

    public function test_the_column_instance_computes_rather_than_the_row_instance(): void
    {
        // Covers KTD2.
        $columns = [new Column('amount')->aggregate(new DoubledSum(factor: 3))];

        $footer = $this->resolve($columns, new FooterRow(new DoubledSum(), AggregateScope::Page, 'Sum'));

        // The column configured factor 3, so 30 * 3 rather than the row instance's factor 2.
        $this->assertSame('90', $footer->rows[0]->values[0]);
    }

    public function test_a_closure_formatter_parameter_renders_the_value_unformatted(): void
    {
        // Covers KTD4.
        $columns = [
            new Column('amount')
                ->currency(locale: 'en_US', currency: static fn (TestModel $model) => 'USD')
                ->aggregate(new Sum()),
        ];

        $footer = $this->resolve($columns, new FooterRow(new Sum(), AggregateScope::Page, 'Sum'));

        $this->assertSame('30', $footer->rows[0]->values[0]);
    }

    public function test_a_row_naming_an_aggregate_no_column_offers_is_all_empty(): void
    {
        $columns = [new Column('name'), new Column('amount')->aggregate(new Sum())];

        $footer = $this->resolve($columns, new FooterRow(new Max(), AggregateScope::Page, 'Max'));

        $this->assertSame(['', ''], $footer->rows[0]->values);
    }

    public function test_the_label_span_covers_a_leading_checkbox_cell(): void
    {
        $columns = [new Column('name'), new Column('amount')->aggregate(new Sum())];

        $without = $this->resolver()->resolve(
            [new FooterRow(new Sum(), AggregateScope::Page, 'Sum')],
            $columns,
            $this->page(10),
            null,
        );

        $with = $this->resolver()->resolve(
            [new FooterRow(new Sum(), AggregateScope::Page, 'Sum')],
            $columns,
            $this->page(10),
            null,
            leadingCells: 1,
        );

        $this->assertSame(1, $without->labelSpan);
        $this->assertSame(2, $with->labelSpan);
    }

    public function test_a_row_may_name_the_column_its_label_sits_in(): void
    {
        $columns = [new Column('name'), new Column('amount')->aggregate(new Sum())];

        $footer = $this->resolve(
            $columns,
            new FooterRow(new Sum(), AggregateScope::Page, 'Sum', labelColumn: 'name'),
        );

        $this->assertSame(0, $footer->rows[0]->labelIndex);
    }

    public function test_a_label_column_that_does_not_exist_falls_back_to_spanning(): void
    {
        $columns = [new Column('amount')->aggregate(new Sum())];

        $footer = $this->resolve(
            $columns,
            new FooterRow(new Sum(), AggregateScope::Page, 'Sum', labelColumn: 'nope'),
        );

        $this->assertNull($footer->rows[0]->labelIndex);
    }

    public function test_a_spanning_label_has_no_label_index(): void
    {
        $columns = [new Column('amount')->aggregate(new Sum())];

        $footer = $this->resolve($columns, new FooterRow(new Sum(), AggregateScope::Page, 'Sum'));

        $this->assertNull($footer->rows[0]->labelIndex);
    }

    public function test_a_total_row_without_a_query_is_empty(): void
    {
        $columns = [new Column('amount')->aggregate(new Sum())];

        $footer = $this->resolver()->resolve(
            [new FooterRow(new Sum(), AggregateScope::Total, 'Total')],
            $columns,
            $this->page(10, 20),
            null,
        );

        $this->assertSame('', $footer->rows[0]->values[0]);
    }

    public function test_no_footer_rows_resolves_to_an_empty_footer(): void
    {
        $footer = $this->resolver()->resolve([], [new Column('amount')], new Collection(), null);

        $this->assertTrue($footer->isEmpty());
    }

    public function test_the_span_covers_every_column_when_nothing_is_aggregated(): void
    {
        $columns = [new Column('name'), new Column('amount')];

        $footer = $this->resolve($columns, new FooterRow(new Sum(), AggregateScope::Page, 'Sum'));

        $this->assertSame(2, $footer->labelSpan);
    }

    public function test_a_closure_label_is_resolved(): void
    {
        $columns = [new Column('amount')->aggregate(new Sum())];

        $footer = $this->resolve($columns, new FooterRow(new Sum(), AggregateScope::Page, static fn () => 'Deferred'));

        $this->assertSame('Deferred', $footer->rows[0]->label);
    }

    public function test_a_formatter_instance_on_the_column_is_used_directly(): void
    {
        $columns = [
            new Column('amount', formatter: new CurrencyFormatter('en_US', 'USD'))->aggregate(new Sum()),
        ];

        $footer = $this->resolve($columns, new FooterRow(new Sum(), AggregateScope::Page, 'Sum'));

        $this->assertStringContainsString('30', $footer->rows[0]->values[0]);
        $this->assertStringContainsString('$', $footer->rows[0]->values[0]);
    }

    private function resolver(): FooterResolver
    {
        return $this->app->make(FooterResolver::class);
    }

    private function resolve(array $columns, FooterRow ...$footerRows): ResolvedFooter
    {
        return $this->resolver()->resolve($footerRows, $columns, $this->page(10, 20), TestModel::query());
    }

    /**
     * @return Collection<int, TestModel>
     */
    private function page(int ...$amounts): Collection
    {
        return new Collection(array_map(static function (int $amount) {
            $model         = new TestModel();
            $model->amount = $amount;

            return $model;
        }, $amounts));
    }

    private function seedRows(int ...$amounts): void
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
