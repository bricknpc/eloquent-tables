<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit;

use Mockery\Mock;
use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\Sort;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Enums\ColumnType;
use BrickNPC\EloquentTables\Enums\TableStyle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use BrickNPC\EloquentTables\Contracts\Formatter;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Formatters\DateFormatter;
use BrickNPC\EloquentTables\Formatters\NumberFormatter;
use BrickNPC\EloquentTables\Formatters\CurrencyFormatter;
use BrickNPC\EloquentTables\Formatters\DateTimeFormatter;

/**
 * @internal
 */
#[CoversClass(Column::class)]
#[UsesClass(Sort::class)]
class ColumnTest extends TestCase
{
    public function test_can_create_a_column_with_only_a_name(): void
    {
        $column = new Column('name');

        $this->assertSame('name', $column->name);
    }

    public function test_can_set_value_using_via_constructor_and_fluent_setter(): void
    {
        $column = new Column(
            name: 'name',
            valueUsing: fn (Model $model) => $model->getKey(),
        );

        $this->assertInstanceOf(\Closure::class, $column->valueUsing);

        $column2 = new Column(
            name: 'name',
        )->valueUsing(fn (Model $model) => $model->getKey());

        $this->assertInstanceOf(\Closure::class, $column2->valueUsing);
    }

    public function test_can_set_label_via_constructor_and_fluent_setter(): void
    {
        $column = new Column(
            name: 'name',
            label: 'Label',
        );

        $this->assertSame('Label', $column->label);

        $column2 = new Column(
            name: 'name',
        )->label('Label');

        $this->assertSame('Label', $column2->label);
    }

    public function test_can_set_sortable_via_constructor_and_fluent_setter(): void
    {
        $column = new Column(
            name: 'name',
            sortable: true,
        );

        $this->assertTrue($column->sortable);

        $column2 = new Column(
            name: 'name',
        )->sortable();

        $this->assertTrue($column2->sortable);
    }

    public function test_can_set_sort_using_via_constructor_and_fluent_setter(): void
    {
        $column = new Column(
            name: 'name',
            sortUsing: fn () => true,
        );

        $this->assertInstanceOf(\Closure::class, $column->sortUsing);

        $column2 = new Column(
            name: 'name',
        )->sortable(sortUsing: fn () => true);

        $this->assertInstanceOf(\Closure::class, $column2->sortUsing);
    }

    public function test_can_set_default_sort_via_constructor_and_fluent_setter(): void
    {
        $column = new Column(
            name: 'name',
            defaultSort: Sort::Asc,
        );

        $this->assertSame(Sort::Asc, $column->defaultSort);

        $column2 = new Column(
            name: 'name',
        )->sortable(default: Sort::Asc);

        $this->assertSame(Sort::Asc, $column2->defaultSort);
    }

    public function test_can_set_searchable_via_constructor_and_fluent_setter(): void
    {
        $column = new Column(
            name: 'name',
            searchable: true,
        );

        $this->assertTrue($column->searchable);

        $column2 = new Column(
            name: 'name',
        )->searchable();

        $this->assertTrue($column2->searchable);
    }

    public function test_can_set_search_using_via_constructor_and_fluent_setter(): void
    {
        $column = new Column(
            name: 'name',
            searchUsing: fn () => true,
        );

        $this->assertInstanceOf(\Closure::class, $column->searchUsing);

        $column2 = new Column(
            name: 'name',
        )->searchable(searchUsing: fn () => true);

        $this->assertInstanceOf(\Closure::class, $column2->searchUsing);
    }

    /**
     * @param Formatter|string<class-string<Formatter>> $formatter
     */
    #[DataProvider('formatterProvider')]
    public function test_can_set_formatter_via_constructor_and_fluent_setter(Formatter|string $formatter): void
    {
        $column = new Column(
            name: 'name',
            formatter: $formatter,
        );

        if (is_string($formatter)) {
            $this->assertSame($formatter, $column->formatter);
        } else {
            $this->assertInstanceOf(get_class($formatter), $column->formatter);
        }

        $column2 = new Column(
            name: 'name',
        )->format($formatter);

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
                public function format(mixed $value, Model $model): \Stringable
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

    public function test_fluent_setters_set_correct_styles(): void
    {
        $column = new Column(name: 'name');
        $this->assertEmpty($column->styles);

        $column2 = new Column(name: 'name', styles: [TableStyle::Dark]);
        $this->assertCount(1, $column2->styles);
        $this->assertSame([TableStyle::Dark], $column2->styles);

        $column3 = new Column(name: 'name', styles: [TableStyle::Dark])->styles(TableStyle::Striped, TableStyle::Active);
        $this->assertCount(3, $column3->styles);
        $this->assertSame([TableStyle::Dark, TableStyle::Striped, TableStyle::Active], $column3->styles);

        $column4 = new Column(name: 'name', styles: [TableStyle::Dark])->styles(TableStyle::Striped, TableStyle::Active)->style(TableStyle::Info);
        $this->assertCount(4, $column4->styles);
        $this->assertSame([TableStyle::Dark, TableStyle::Striped, TableStyle::Active, TableStyle::Info], $column4->styles);
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
        $searchUsing = fn (Request $request, Builder $query, string $searchQuery) => $query->where('other', '=', '%' . $searchQuery . '%');

        $column = new Column('name')->searchable(searchUsing: $searchUsing);

        /** @var Mock&Request $request */
        $request = $this->mock(Request::class);

        /** @var Builder&Mock $builder */
        $builder     = $this->mock(Builder::class);
        $searchQuery = 'test';

        $builder->shouldReceive('where')->once()->with('other', '=', '%test%');

        $column->search($request, $builder, $searchQuery);
    }
}
