<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\Sort;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use BrickNPC\EloquentTables\Builders\ColumnLabelViewBuilder;

/**
 * @internal
 */
#[CoversClass(ColumnLabelViewBuilder::class)]
#[UsesClass(Column::class)]
#[UsesClass(Config::class)]
class ColumnLabelViewBuilderTest extends TestCase
{
    public function test_it_returns_the_correct_view(): void
    {
        /** @var ColumnLabelViewBuilder $builder */
        $builder = $this->app->make(ColumnLabelViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $column  = new Column('name');

        $view = $builder->build($request, $column);

        $this->assertSame('eloquent-tables::table.th', $view->name());
    }

    #[DataProvider('sortOrderProvider')]
    public function test_it_builds_the_correct_sort_url(?Sort $currentOrder, ?Sort $nextOrder): void
    {
        /** @var ColumnLabelViewBuilder $builder */
        $builder = $this->app->make(ColumnLabelViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        if (null !== $currentOrder) {
            $request->query->set('sort', ['name' => $currentOrder->value]);
        }

        $column = new Column('name');

        $view = $builder->build($request, $column);

        $this->assertArrayHasKey('href', $view->getData());
        if (null === $nextOrder) {
            $this->assertSame('http://localhost/?', $view->getData()['href']);
        } else {
            $this->assertSame('http://localhost/?sort%5Bname%5D=' . $nextOrder->value, $view->getData()['href']);
        }
    }

    public static function sortOrderProvider(): \Generator
    {
        yield [
            null,
            Sort::Asc,
        ];

        yield [
            Sort::Asc,
            Sort::Desc,
        ];

        yield [
            Sort::Desc,
            null,
        ];
    }
}
