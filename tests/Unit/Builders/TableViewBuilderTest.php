<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Column;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Enums\TableStyle;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Attributes\Layout;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Services\LayoutFinder;
use BrickNPC\EloquentTables\Builders\TableViewBuilder;
use BrickNPC\EloquentTables\Factories\FormatterFactory;
use BrickNPC\EloquentTables\Builders\ColumnLabelViewBuilder;
use BrickNPC\EloquentTables\Builders\ColumnValueViewBuilder;

/**
 * @internal
 */
#[CoversClass(TableViewBuilder::class)]
#[UsesClass(ColumnLabelViewBuilder::class)]
#[UsesClass(ColumnValueViewBuilder::class)]
#[UsesClass(FormatterFactory::class)]
#[UsesClass(LayoutFinder::class)]
#[UsesClass(Table::class)]
#[UsesClass(Column::class)]
#[UsesClass(TableStyle::class)]
#[UsesClass(Layout::class)]
class TableViewBuilderTest extends TestCase
{
    public function test_it_returns_the_correct_view(): void
    {
        /** @var TableViewBuilder $builder */
        $builder = $this->app->make(TableViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $table   = new class extends Table {
            public function columns(): array
            {
                return [];
            }

            public function query(): Builder
            {
                return Model::query();
            }
        };

        $view = $builder->build($table, $request);

        $this->assertSame('eloquent-tables::table', $view->name());
    }

    public function test_it_returns_the_correct_view_when_a_layout_is_specified_via_attribute(): void
    {
        /** @var TableViewBuilder $builder */
        $builder = $this->app->make(TableViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $table   = new #[Layout('app.layout')] class extends Table {
            public function columns(): array
            {
                return [];
            }

            public function query(): Builder
            {
                return Model::query();
            }
        };

        $view = $builder->build($table, $request);

        $this->assertSame('eloquent-tables::table-with-layout', $view->name());
    }

    public function test_it_returns_the_correct_view_when_a_layout_is_specified_via_method(): void
    {
        /** @var TableViewBuilder $builder */
        $builder = $this->app->make(TableViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $table   = new class extends Table {
            public function columns(): array
            {
                return [];
            }

            public function query(): Builder
            {
                return Model::query();
            }

            public function layout(): Layout
            {
                return new Layout('app.layout');
            }
        };

        $view = $builder->build($table, $request);

        $this->assertSame('eloquent-tables::table-with-layout', $view->name());
    }

    public function test_it_builds_table_styles_correctly(): void
    {
        /** @var TableViewBuilder $builder */
        $builder = $this->app->make(TableViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $table = new class extends Table {
            public function columns(): array
            {
                return [];
            }

            public function query(): Builder
            {
                return Model::query();
            }
        };

        $view = $builder->build($table, $request);

        $this->assertArrayHasKey('tableStyles', $view->getData());
        $this->assertEmpty($view->getData()['tableStyles']);
    }

    private function injectDependencies(Table $table): void
    {
        $table->request = $this->app->make('request');
        $table->trans   = $this->app->make('translator');
        $table->builder = $this->app->make(TableViewBuilder::class);
    }
}
