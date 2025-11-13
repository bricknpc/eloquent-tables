<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit;

use Mockery\Mock;
use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use Illuminate\Contracts\View\View;
use BrickNPC\EloquentTables\Enums\Sort;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
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

    public function test_it_renders_the_column_label_when_label_is_set(): void
    {
        $column = new Column(name: 'name', label: 'Column Label');

        $rendered = $column->renderLabel(
            $this->app->make(Request::class),
            $this->app->make('view'),
        );

        $this->assertInstanceOf(View::class, $rendered);
        $this->assertStringContainsString('Column Label', $rendered->render());
        $this->assertSame('eloquent-tables::column-label', $rendered->getName());
    }

    public function test_it_renders_the_name_as_label_when_no_label_is_set(): void
    {
        $column = new Column(name: 'name');

        $rendered = $column->renderLabel(
            $this->app->make(Request::class),
            $this->app->make('view'),
        );

        $this->assertInstanceOf(View::class, $rendered);
        $this->assertStringContainsString('Name', $rendered->render());
        $this->assertSame('eloquent-tables::column-label', $rendered->getName());
    }

    #[DataProvider('columnOptionsProvider')]
    public function test_it_adds_column_options_when_rendering_the_label(array $columnOptions, array $expectedData): void
    {
        $column = new Column(...$columnOptions);

        $rendered = $column->renderLabel(
            $this->app->make(Request::class),
            $this->app->make('view'),
        );

        $this->assertArrayIsIdenticalToArrayIgnoringListOfKeys($expectedData, $rendered->getData(), ['href']);
    }

    public static function columnOptionsProvider(): \Generator
    {
        yield [
            [
                'name'       => 'name',
                'label'      => 'Column Label',
                'sortable'   => false,
                'searchable' => false,
            ],
            [
                'label'         => 'Column Label',
                'sortable'      => false,
                'searchable'    => false,
                'isSorted'      => false,
                'sortDirection' => null,
            ],
        ];
    }
}
