<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\ColumnType;
use BrickNPC\EloquentTables\Enums\TableStyle;
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
#[UsesClass(ColumnType::class)]
#[UsesClass(TableStyle::class)]
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

    public function test_it_renders_the_correct_theme(): void
    {
        config()->set('eloquent-tables.theme', Theme::Bootstrap5);

        /** @var ColumnValueViewBuilder $builder */
        $builder = $this->app->make(ColumnValueViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $model   = new class extends Model {};
        $column  = new Column('name');

        $view = $builder->build($request, $column, $model);

        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('theme', $view->getData());
        $this->assertSame(Theme::Bootstrap5, $view->getData()['theme']);
    }

    public function test_it_renders_the_correct_styles(): void
    {
        /** @var ColumnValueViewBuilder $builder */
        $builder = $this->app->make(ColumnValueViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $model   = new class extends Model {};
        $column  = new Column('name')->styles(TableStyle::Active, TableStyle::Dark);

        $view = $builder->build($request, $column, $model);

        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('styles', $view->getData());
        $this->assertSame('table-active table-dark', $view->getData()['styles']);
    }

    public function test_it_renders_the_correct_type(): void
    {
        /** @var ColumnValueViewBuilder $builder */
        $builder = $this->app->make(ColumnValueViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $model   = new class extends Model {};
        $column  = new Column('name')->boolean();

        $view = $builder->build($request, $column, $model);

        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('type', $view->getData());
        $this->assertSame(ColumnType::Boolean, $view->getData()['type']);
    }

    public function test_it_renders_the_correct_icons(): void
    {
        /** @var ColumnValueViewBuilder $builder */
        $builder = $this->app->make(ColumnValueViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $model   = new class extends Model {};
        $column  = new Column('name');

        $view = $builder->build($request, $column, $model);

        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('checkIcon', $view->getData());
        $this->assertSame('✓', $view->getData()['checkIcon']);
        $this->assertArrayHasKey('crossIcon', $view->getData());
        $this->assertSame('✗', $view->getData()['crossIcon']);
    }
}
