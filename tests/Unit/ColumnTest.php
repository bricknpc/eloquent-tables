<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit;

use Mockery\Mock;
use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\Sort;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Aggregates\Sum;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Enums\CellStyle;
use BrickNPC\EloquentTables\Aggregates\Count;
use BrickNPC\EloquentTables\Enums\ColumnType;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\StyleFamily;
use BrickNPC\EloquentTables\Enums\StyleTarget;
use BrickNPC\EloquentTables\Enums\TableRegion;
use PHPUnit\Framework\Attributes\DataProvider;
use BrickNPC\EloquentTables\Contracts\Formatter;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\Formatters\DateFormatter;
use BrickNPC\EloquentTables\Concerns\AggregatesValues;
use BrickNPC\EloquentTables\Formatters\NumberFormatter;
use BrickNPC\EloquentTables\Styles\Contexts\CellContext;
use BrickNPC\EloquentTables\Formatters\CurrencyFormatter;
use BrickNPC\EloquentTables\Formatters\DateTimeFormatter;

/**
 * @internal
 */
#[CoversClass(Column::class)]
#[UsesClass(Sort::class)]
#[UsesClass(StyleSet::class)]
#[UsesClass(CellContext::class)]
#[UsesClass(CellStyle::class)]
#[UsesClass(StyleTarget::class)]
#[UsesClass(StyleFamily::class)]
#[UsesClass(TableRegion::class)]
#[UsesClass(Sum::class)]
#[UsesClass(Count::class)]
#[UsesClass(AggregatesValues::class)]
class ColumnTest extends TestCase
{
    public function test_can_create_a_column_with_only_a_name(): void
    {
        $column = new Column('name');

        $this->assertSame('name', $column->name);
    }

    public function test_can_set_value_using_via_constructor_and_fluent_setter(): void
    {
        $column = new Column(name: 'name', valueUsing: static fn(Model $model) => $model->getKey());

        $this->assertInstanceOf(\Closure::class, $column->valueUsing);

        $column2 = new Column(name: 'name')->valueUsing(static fn(Model $model) => $model->getKey());

        $this->assertInstanceOf(\Closure::class, $column2->valueUsing);
    }

    public function test_can_set_label_via_constructor_and_fluent_setter(): void
    {
        $column = new Column(name: 'name', label: 'Label');

        $this->assertSame('Label', $column->label);

        $column2 = new Column(name: 'name')->label('Label');

        $this->assertSame('Label', $column2->label);
    }

    public function test_can_set_sortable_via_constructor_and_fluent_setter(): void
    {
        $column = new Column(name: 'name', sortable: true);

        $this->assertTrue($column->sortable);

        $column2 = new Column(name: 'name')->sortable();

        $this->assertTrue($column2->sortable);
    }

    public function test_can_set_sort_using_via_constructor_and_fluent_setter(): void
    {
        $column = new Column(name: 'name', sortUsing: static fn() => true);

        $this->assertInstanceOf(\Closure::class, $column->sortUsing);

        $column2 = new Column(name: 'name')->sortable(sortUsing: static fn() => true);

        $this->assertInstanceOf(\Closure::class, $column2->sortUsing);
    }

    public function test_can_set_default_sort_via_constructor_and_fluent_setter(): void
    {
        $column = new Column(name: 'name', defaultSort: Sort::Asc);

        $this->assertSame(Sort::Asc, $column->defaultSort);

        $column2 = new Column(name: 'name')->sortable(default: Sort::Asc);

        $this->assertSame(Sort::Asc, $column2->defaultSort);
    }

    public function test_can_set_searchable_via_constructor_and_fluent_setter(): void
    {
        $column = new Column(name: 'name', searchable: true);

        $this->assertTrue($column->searchable);

        $column2 = new Column(name: 'name')->searchable();

        $this->assertTrue($column2->searchable);
    }

    public function test_can_set_search_using_via_constructor_and_fluent_setter(): void
    {
        $column = new Column(name: 'name', searchUsing: static fn() => true);

        $this->assertInstanceOf(\Closure::class, $column->searchUsing);

        $column2 = new Column(name: 'name')->searchable(searchUsing: static fn() => true);

        $this->assertInstanceOf(\Closure::class, $column2->searchUsing);
    }

    /**
     * @param Formatter|string<class-string<Formatter>> $formatter
     */
    #[DataProvider('formatterProvider')]
    public function test_can_set_formatter_via_constructor_and_fluent_setter(Formatter|string $formatter): void
    {
        $column = new Column(name: 'name', formatter: $formatter);

        if (is_string($formatter)) {
            $this->assertSame($formatter, $column->formatter);
        } else {
            $this->assertInstanceOf(get_class($formatter), $column->formatter);
        }

        $column2 = new Column(name: 'name')->format($formatter);

        if (is_string($formatter)) {
            $this->assertSame($formatter, $column2->formatter);
        } else {
            $this->assertInstanceOf(get_class($formatter), $column2->formatter);
        }
    }

    public static function formatterProvider(): \Generator
    {
        yield [
            new class implements Formatter {
                public function format(mixed $value, ?Model $model = null): \Stringable
                {
                    return str('formatted');
                }
            },
        ];

        yield [
            NumberFormatter::class,
        ];

        yield [
            CurrencyFormatter::class,
        ];

        yield [
            DateFormatter::class,
        ];

        yield [
            DateTimeFormatter::class,
        ];
    }

    public function test_fluent_setters_set_correct_formatter(): void
    {
        $column = new Column('name')->date();
        $this->assertSame(DateFormatter::class, $column->formatter);

        $column2 = new Column('name')->dateTime();
        $this->assertSame(DateTimeFormatter::class, $column2->formatter);

        $column3 = new Column('name')->currency();
        $this->assertSame(CurrencyFormatter::class, $column3->formatter);

        $column4 = new Column('name')->number();
        $this->assertSame(NumberFormatter::class, $column4->formatter);

        $column5 = new Column('name')->float();
        $this->assertSame(NumberFormatter::class, $column5->formatter);
    }

    #[DataProvider('customFormatterPropertiesProvider')]
    public function test_using_custom_properties_for_formatters_saves_them_correctly(
        string $method,
        array $properties,
        array $expected,
    ): void {
        $column = new Column('name');

        call_user_func_array([$column, $method], $properties);

        $this->assertSame($expected, $column->getFormatterParameters());
    }

    public static function customFormatterPropertiesProvider(): \Generator
    {
        yield [
            'number',
            [
                'decimals' => 2,
                'locale'   => 'nl',
            ],
            [
                'decimals' => 2,
                'locale'   => 'nl',
            ],
        ];

        yield [
            'number',
            [
                'decimals' => 5,
            ],
            [
                'decimals' => 5,
            ],
        ];

        yield [
            'number',
            [
                'locale' => 'de',
            ],
            [
                'decimals' => 0,
                'locale'   => 'de',
            ],
        ];

        yield [
            'float',
            [
                'decimals' => 2,
                'locale'   => 'en',
            ],
            [
                'decimals' => 2,
                'locale'   => 'en',
            ],
        ];

        yield [
            'float',
            [
                'decimals' => 1,
            ],
            [
                'decimals' => 1,
            ],
        ];

        yield [
            'float',
            [
                'locale' => 'de',
            ],
            [
                'decimals' => 2,
                'locale'   => 'de',
            ],
        ];

        yield [
            'currency',
            [
                'currency' => 'USD',
                'locale'   => 'en',
            ],
            [
                'currency' => 'USD',
                'locale'   => 'en',
            ],
        ];

        yield [
            'currency',
            [
                'currency' => 'EUR',
            ],
            [
                'currency' => 'EUR',
            ],
        ];

        yield [
            'currency',
            [
                'locale' => 'de',
            ],
            [
                'locale' => 'de',
            ],
        ];

        yield [
            'currency',
            [],
            [],
        ];

        yield 'date with a locale and timezone' => [
            'date',
            [
                'locale'   => 'nl',
                'timezone' => 'Europe/Amsterdam',
            ],
            [
                'locale'   => 'nl',
                'timezone' => 'Europe/Amsterdam',
            ],
        ];

        yield 'dateTime with a DateTimeZone object' => [
            'dateTime',
            [
                'timezone' => $timezone = new \DateTimeZone('Asia/Tokyo'),
            ],
            [
                'timezone' => $timezone,
            ],
        ];

        yield 'date without arguments stores nothing' => [
            'date',
            [],
            [],
        ];

        yield 'currency from closures' => [
            'currency',
            [
                'currency' => $currency = static fn(Model $model) => 'USD',
                'locale'   => $locale = static fn(Model $model) => 'en_US',
            ],
            [
                'currency' => $currency,
                'locale'   => $locale,
            ],
        ];

        yield 'number with closure decimals' => [
            'number',
            [
                'decimals' => $decimals = static fn(Model $model) => 3,
            ],
            [
                'decimals' => $decimals,
            ],
        ];

        yield 'float with closure decimals' => [
            'float',
            [
                'decimals' => $floatDecimals = static fn(Model $model) => 4,
            ],
            [
                'decimals' => $floatDecimals,
            ],
        ];

        yield 'dateTime with closure locale and timezone' => [
            'dateTime',
            [
                'locale'   => $dtLocale = static fn(Model $model) => 'ja_JP',
                'timezone' => $dtTimezone = static fn(Model $model) => 'Asia/Tokyo',
            ],
            [
                'locale'   => $dtLocale,
                'timezone' => $dtTimezone,
            ],
        ];
    }

    public function test_fluent_setters_set_correct_column_type(): void
    {
        $column = new Column('name');
        $this->assertSame(ColumnType::Text, $column->type);

        $column2 = new Column(name: 'name', type: ColumnType::Boolean);
        $this->assertSame(ColumnType::Boolean, $column2->type);

        $column3 = new Column(name: 'name')->type(ColumnType::Boolean);
        $this->assertSame(ColumnType::Boolean, $column3->type);

        $column4 = new Column(name: 'name')->boolean();
        $this->assertSame(ColumnType::Boolean, $column4->type);

        $column4 = new Column(name: 'name')->checkbox();
        $this->assertSame(ColumnType::Checkbox, $column4->type);
    }

    public function test_a_column_declares_no_styles_by_default(): void
    {
        $this->assertNull(new Column(name: 'name')->style);
    }

    public function test_a_column_offers_no_aggregates_by_default(): void
    {
        $this->assertSame([], new Column('amount')->aggregates);
    }

    public function test_aggregates_can_be_declared_through_the_constructor(): void
    {
        $sum = new Sum();

        $this->assertSame([$sum], new Column(name: 'amount', aggregates: [$sum])->aggregates);
    }

    public function test_aggregates_can_be_declared_fluently(): void
    {
        $sum   = new Sum();
        $count = new Count();

        $this->assertSame([$sum, $count], new Column('amount')->aggregate($sum, $count)->aggregates);
    }

    public function test_aggregate_returns_the_column_for_chaining(): void
    {
        $column = new Column('amount');

        $this->assertSame($column, $column->aggregate(new Sum()));
    }

    public function test_declaring_aggregates_twice_appends_rather_than_replaces(): void
    {
        $sum   = new Sum();
        $count = new Count();

        $column = new Column('amount')
            ->aggregate($sum)
            ->aggregate($count);

        $this->assertSame([$sum, $count], $column->aggregates);
    }

    public function test_styles_can_be_declared_through_the_constructor(): void
    {
        $column = new Column(name: 'name', style: new StyleSet(CellStyle::AlignRight));

        $this->assertSame([CellStyle::AlignRight], $column->style?->resolve($this->cellContext($column)));
    }

    public function test_styles_can_be_declared_fluently(): void
    {
        $column = new Column(name: 'name')->style(CellStyle::AlignRight, CellStyle::FontBold);

        $this->assertSame(
            [CellStyle::AlignRight, CellStyle::FontBold],
            $column->style?->resolve($this->cellContext($column)),
        );
    }

    public function test_declaring_styles_twice_merges_rather_than_replaces(): void
    {
        $column = new Column(name: 'name', style: new StyleSet(CellStyle::AlignRight))->style(CellStyle::FontBold);

        $this->assertSame(
            [CellStyle::AlignRight, CellStyle::FontBold],
            $column->style?->resolve($this->cellContext($column)),
        );
    }

    public function test_a_closure_can_be_declared_alongside_static_styles(): void
    {
        $column = new Column(name: 'name')->style(CellStyle::AlignRight, static fn() => CellStyle::BackgroundDanger);

        $this->assertSame(
            [CellStyle::AlignRight, CellStyle::BackgroundDanger],
            $column->style?->resolve($this->cellContext($column)),
        );
    }

    public function test_non_searchable_column_does_not_search_in_column(): void
    {
        $column = new Column('name');

        /** @var Mock&Request $request */
        $request = $this->mock(Request::class);

        /** @var Builder&Mock $builder */
        $builder     = $this->mock(Builder::class);
        $searchQuery = 'test';

        $builder->shouldReceive('where')->never();

        $column->search($request, $builder, $searchQuery);
    }

    public function test_default_search_algorithm_searches_in_column(): void
    {
        $column = new Column('name')->searchable();

        /** @var Mock&Request $request */
        $request = $this->mock(Request::class);

        /** @var Builder&Mock $builder */
        $builder     = $this->mock(Builder::class);
        $searchQuery = 'test';

        $builder->shouldReceive('where')->once()->with('name', 'like', '%test%');

        $column->search($request, $builder, $searchQuery);
    }

    public function test_custom_search_algorithm_searches_in_column(): void
    {
        $searchUsing = static fn(Request $request, Builder $query, string $searchQuery) => $query->where(
            'other',
            '=',
            '%' . $searchQuery . '%',
        );

        $column = new Column('name')->searchable(searchUsing: $searchUsing);

        /** @var Mock&Request $request */
        $request = $this->mock(Request::class);

        /** @var Builder&Mock $builder */
        $builder     = $this->mock(Builder::class);
        $searchQuery = 'test';

        $builder->shouldReceive('where')->once()->with('other', '=', '%test%');

        $column->search($request, $builder, $searchQuery);
    }

    /**
     * @param Column<TestModel> $column
     */
    private function cellContext(Column $column): CellContext
    {
        /** @var Request $request */
        $request = $this->app->make('request');

        return new CellContext($request, $column);
    }
}
