<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Footers;

use BrickNPC\EloquentTables\Table;
use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Aggregates\Sum;
use BrickNPC\EloquentTables\Enums\RowStyle;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Aggregates\Count;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Columns\ColumnValue;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Enums\AggregateScope;
use BrickNPC\EloquentTables\Styles\StyleResolver;
use BrickNPC\EloquentTables\Footers\FooterRenderer;
use BrickNPC\EloquentTables\Footers\FooterResolver;
use BrickNPC\EloquentTables\ValueObjects\FooterRow;
use BrickNPC\EloquentTables\Concerns\AggregatesValues;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Factories\FormatterFactory;
use BrickNPC\EloquentTables\ValueObjects\ResolvedFooter;
use BrickNPC\EloquentTables\ValueObjects\ResolvedFooterRow;

/**
 * @internal
 */
#[CoversClass(FooterRenderer::class)]
#[UsesClass(FooterResolver::class)]
#[UsesClass(Column::class)]
#[UsesClass(ColumnValue::class)]
#[UsesClass(FooterRow::class)]
#[UsesClass(ResolvedFooter::class)]
#[UsesClass(ResolvedFooterRow::class)]
#[UsesClass(AggregateScope::class)]
#[UsesClass(RowStyle::class)]
#[UsesClass(Sum::class)]
#[UsesClass(Count::class)]
#[UsesClass(AggregatesValues::class)]
#[UsesClass(StyleResolver::class)]
#[UsesClass(FormatterFactory::class)]
#[UsesClass(Table::class)]
#[UsesClass(Config::class)]
class FooterRendererTest extends TestCase
{
    public function test_a_table_declaring_no_footer_resolves_to_an_empty_footer(): void
    {
        $footer = $this->renderer()->build($this->tableWithoutFooter(), [new Column('amount')], $this->page(10), null);

        $this->assertTrue($footer->isEmpty());
        $this->assertSame(0, $footer->labelSpan);
    }

    public function test_it_resolves_the_rows_a_table_declares(): void
    {
        $columns = [new Column('amount')->aggregate(new Sum())];

        $footer = $this->renderer()->build($this->tableWithFooter(), $columns, $this->page(10, 20), null);

        $this->assertFalse($footer->isEmpty());
        $this->assertCount(1, $footer->rows);
        $this->assertSame('Page total', $footer->rows[0]->label);
        $this->assertSame('30', $footer->rows[0]->values[0]);
    }

    public function test_it_passes_the_leading_cell_count_through(): void
    {
        $columns = [new Column('name'), new Column('amount')->aggregate(new Sum())];

        $withCheckbox = $this->renderer()->build($this->tableWithFooter(), $columns, $this->page(10), null, 1);
        $without      = $this->renderer()->build($this->tableWithFooter(), $columns, $this->page(10), null, 0);

        $this->assertSame(2, $withCheckbox->labelSpan);
        $this->assertSame(1, $without->labelSpan);
    }

    public function test_it_resolves_declared_row_styles(): void
    {
        // Covers R11.
        $columns = [new Column('amount')->aggregate(new Sum())];

        $footer = $this->renderer()->build($this->tableWithStyledFooter(), $columns, $this->page(10), null);

        $this->assertSame('table-primary', $footer->rows[0]->styles);
    }

    public function test_a_row_without_styles_carries_no_classes(): void
    {
        $columns = [new Column('amount')->aggregate(new Sum())];

        $footer = $this->renderer()->build($this->tableWithFooter(), $columns, $this->page(10), null);

        $this->assertSame('', $footer->rows[0]->styles);
    }

    private function renderer(): FooterRenderer
    {
        return $this->app->make(FooterRenderer::class);
    }

    private function tableWithoutFooter(): Table
    {
        return new class extends Table {
            public function columns(): array
            {
                return [new Column('amount')];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };
    }

    private function tableWithFooter(): Table
    {
        return new class extends Table {
            public function columns(): array
            {
                return [new Column('amount')->aggregate(new Sum())];
            }

            public function footer(): array
            {
                return [new FooterRow(new Sum(), AggregateScope::Page, 'Page total')];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };
    }

    private function tableWithStyledFooter(): Table
    {
        return new class extends Table {
            public function columns(): array
            {
                return [new Column('amount')->aggregate(new Sum())];
            }

            public function footer(): array
            {
                return [
                    new FooterRow(new Sum(), AggregateScope::Page, 'Page total', styles: [RowStyle::Primary]),
                ];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };
    }

    /**
     * @return Collection<int, TestModel>
     */
    private function page(int ...$amounts): Collection
    {
        return new Collection(array_map(function (int $amount) {
            $model         = new TestModel();
            $model->amount = $amount;

            return $model;
        }, $amounts));
    }
}
