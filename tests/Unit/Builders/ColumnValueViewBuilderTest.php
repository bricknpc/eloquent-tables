<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Factories\FormatterFactory;
use BrickNPC\EloquentTables\Tests\Resources\TestFormatter;
use BrickNPC\EloquentTables\Builders\ColumnValueViewBuilder;

/**
 * @internal
 */
#[CoversClass(ColumnValueViewBuilder::class)]
#[UsesClass(FormatterFactory::class)]
#[UsesClass(Column::class)]
#[UsesClass(Config::class)]
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

        $this->assertSame('eloquent-tables::table.td', $view->name());
    }

    public function test_it_builds_and_uses_formatter(): void
    {
        /** @var ColumnValueViewBuilder $builder */
        $builder = $this->app->make(ColumnValueViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $model   = new class extends Model {};
        $column  = new Column('name')->format(TestFormatter::class);

        $view = $builder->build($request, $column, $model);

        $this->assertSame('eloquent-tables::table.td', $view->name());
        $this->assertStringContainsString('formatted', $view->render());
    }

    public function test_it_uses_formatter(): void
    {
        /** @var ColumnValueViewBuilder $builder */
        $builder = $this->app->make(ColumnValueViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $model   = new class extends Model {};
        $column  = new Column('name')->format(new TestFormatter());

        $view = $builder->build($request, $column, $model);

        $this->assertSame('eloquent-tables::table.td', $view->name());
        $this->assertStringContainsString('formatted', $view->render());
    }
}
