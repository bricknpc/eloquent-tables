<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Factories\FormatterFactory;
use BrickNPC\EloquentTables\Builders\ColumnValueViewBuilder;

/**
 * @internal
 */
#[CoversClass(ColumnValueViewBuilder::class)]
#[UsesClass(FormatterFactory::class)]
#[UsesClass(Column::class)]
class ColumnValueViewBuilderTest extends TestCase
{
    public function test_it_returns_the_correct_view(): void
    {
        /** @var ColumnValueViewBuilder $builder */
        $builder = $this->app->make(ColumnValueViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $model   = new class extends Model {};
        $column  = new Column('name');

        $view = $builder->build($request, $column, $model);

        $this->assertSame('eloquent-tables::column-value', $view->name());
    }
}
