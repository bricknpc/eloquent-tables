<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Filters;

use Mockery\Mock;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Filters\Filter;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;

/**
 * @internal
 */
#[CoversClass(Filter::class)]
class FilterTest extends TestCase
{
    public function test_it_uses_name_for_default_filter(): void
    {
        $filter = new Filter('name', []);

        /** @var Request $request */
        $request = $this->app->make('request');

        /** @var Builder|Mock $query */
        $query = $this->mock(Builder::class);

        $value = 'search';

        $query
            ->expects('where')
            ->with('name', '=', 'search')
            ->once()
        ;

        $filter($request, $query, $value);
    }

    public function test_it_uses_custom_filter(): void
    {
        $filter = new Filter('name', [])
            ->filter(
                fn (Request $request, Builder $query, string $value) => $query->where('custom_column', '!=', $value),
            )
        ;

        /** @var Request $request */
        $request = $this->app->make('request');

        /** @var Builder|Mock $query */
        $query = $this->mock(Builder::class);

        $value = 'search value';

        $query
            ->expects('where')
            ->with('custom_column', '!=', 'search value')
            ->once()
        ;

        $filter($request, $query, $value);
    }

    public function test_it_returns_the_correct_view(): void
    {
        $filter = new Filter('name', []);

        $this->assertSame('eloquent-tables::filter.filter', $filter->view());
    }

    #[DataProvider('optionValueProvider')]
    public function test_it_returns_option_values(Collection|iterable $options, iterable $expectedOptions): void
    {
        $filter = new Filter('name', $options);

        $this->assertSame($expectedOptions, $filter->options());
    }

    public static function optionValueProvider(): \Generator
    {
        yield [
            ['first', 'second', 3],
            [0 => 'first', 1 => 'second', 2 => '3'],
        ];

        $model1       = new TestModel();
        $model1->id   = 1;
        $model1->name = 'name 1';

        $model2       = new TestModel();
        $model2->id   = 17;
        $model2->name = 'name 17';

        yield [
            [$model1, $model2],
            [
                '1'  => 'name 1',
                '17' => 'name 17',
            ],
        ];
    }
}
