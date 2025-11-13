<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Builders\ColumnLabelViewBuilder;

/**
 * @internal
 */
#[CoversClass(ColumnLabelViewBuilder::class)]
#[UsesClass(Column::class)]
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

        $this->assertSame('eloquent-tables::column-label', $view->name());
    }
}
