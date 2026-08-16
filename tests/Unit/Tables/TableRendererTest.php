<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Tables;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Enums\PageStyle;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\TableStyle;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Attributes\Layout;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\Actions\Intents\Http;
use BrickNPC\EloquentTables\Builders\RowsBuilder;
use BrickNPC\EloquentTables\Tables\TableRenderer;
use BrickNPC\EloquentTables\Services\LayoutFinder;
use BrickNPC\EloquentTables\Actions\ActionRenderer;
use BrickNPC\EloquentTables\Filters\FilterRenderer;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Concerns\WithPagination;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Services\RouteModelBinder;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Factories\FormatterFactory;
use BrickNPC\EloquentTables\Columns\ColumnLabelRenderer;
use BrickNPC\EloquentTables\Columns\ColumnValueRenderer;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer;

/**
 * @internal
 */
#[CoversClass(TableRenderer::class)]
#[CoversClass(WithPagination::class)]
#[UsesClass(ColumnLabelRenderer::class)]
#[UsesClass(ColumnValueRenderer::class)]
#[UsesClass(FormatterFactory::class)]
#[UsesClass(LayoutFinder::class)]
#[UsesClass(Table::class)]
#[UsesClass(Column::class)]
#[UsesClass(TableStyle::class)]
#[UsesClass(Layout::class)]
#[UsesClass(WithPagination::class)]
#[UsesClass(Theme::class)]
#[UsesClass(Config::class)]
#[UsesClass(RowsBuilder::class)]
#[UsesClass(FilterRenderer::class)]
#[UsesClass(RouteModelBinder::class)]
#[UsesClass(PageStyle::class)]
#[UsesClass(ActionRenderer::class)]
#[UsesClass(Action::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(ActionIntent::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(Http::class)]
#[UsesClass(RenderBuffer::class)]
#[UsesClass(LazyValue::class)]
class TableRendererTest extends TestCase
{
    public function test_it_returns_the_correct_view(): void
    {
        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $table   = new class extends Table {
            public function columns(): array
            {
                return [];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        $view = $builder->build($table, $request);

        $this->assertSame('eloquent-tables::table', $view->name());
    }

    public function test_it_returns_the_correct_view_when_a_layout_is_specified_via_attribute(): void
    {
        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $table   = new #[Layout('app.layout')] class extends Table {
            public function columns(): array
            {
                return [];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        $view = $builder->build($table, $request);

        $this->assertSame('eloquent-tables::table-with-layout', $view->name());
    }

    public function test_it_returns_the_correct_view_when_a_layout_is_specified_via_method(): void
    {
        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $table   = new class extends Table {
            public function columns(): array
            {
                return [];
            }

            public function query(): Builder
            {
                return TestModel::query();
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
        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $table = new class extends Table {
            public function columns(): array
            {
                return [];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }
        };

        $view = $builder->build($table, $request);

        $this->assertArrayHasKey('tableStyles', $view->getData());
        $this->assertEmpty($view->getData()['tableStyles']);
    }

    public function test_it_gets_all_results_without_pagination(): void
    {
        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        // Create 100 test models
        for ($i = 0; $i < 100; ++$i) {
            DB::table('test_models')->insert([
                'name'       => sprintf('Test Model %d', $i),
                'email'      => sprintf('test-email-%d@test.com', $i),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $table = new class extends Table {
            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [];
            }
        };

        $view = $builder->build($table, $request);

        $viewData = $view->getData();

        $this->assertArrayHasKey('rows', $viewData);
        $this->assertInstanceOf(Collection::class, $viewData['rows']);
        $this->assertCount(100, $viewData['rows']);
        $this->assertNull($viewData['links']);
    }

    public function test_it_gets_paginated_results_with_pagination(): void
    {
        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        // Create 100 test models
        for ($i = 0; $i < 100; ++$i) {
            DB::table('test_models')->insert([
                'name'       => sprintf('Test Model %d', $i),
                'email'      => sprintf('test-email-%d@test.com', $i),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $table = new class extends Table {
            use WithPagination;

            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [];
            }
        };

        $view = $builder->build($table, $request);

        $viewData = $view->getData();

        $this->assertArrayHasKey('rows', $viewData);
        $this->assertInstanceOf(Collection::class, $viewData['rows']);
        $this->assertCount(15, $viewData['rows']);
        $this->assertNotNull($viewData['links']);
    }

    public function test_it_sets_pagination_options_when_using_pagination(): void
    {
        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $table = new class extends Table {
            use WithPagination;

            protected int $perPage          = 25;
            protected string $perPageName   = 'per_page';
            protected string $pageName      = 'page';
            protected array $perPageOptions = [10, 25, 50];

            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [];
            }
        };

        $view = $builder->build($table, $request);

        $viewData = $view->getData();

        $this->assertArrayHasKey('perPage', $viewData);
        $this->assertSame(25, $viewData['perPage']);

        $this->assertArrayHasKey('perPageName', $viewData);
        $this->assertSame('per_page', $viewData['perPageName']);

        $this->assertArrayHasKey('perPageOptions', $viewData);
        $this->assertSame([10, 25, 50], $viewData['perPageOptions']);
    }

    public function test_it_shows_search_form_when_there_are_searchable_columns(): void
    {
        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $table = new class extends Table {
            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [
                    new Column('name')->searchable(),
                ];
            }
        };

        $view = $builder->build($table, $request);

        $viewData = $view->getData();

        $this->assertArrayHasKey('showSearchForm', $viewData);
        $this->assertTrue($viewData['showSearchForm']);
        $this->assertArrayHasKey('tableSearchUrl', $viewData);
        $this->assertArrayHasKey('searchQuery', $viewData);
        $this->assertArrayHasKey('searchQueryName', $viewData);
    }

    public function test_the_bulk_action_column_uses_the_default_width(): void
    {
        $html = $this->renderTableWithBulkActions();

        $this->assertStringContainsString('style="width: 5%;"', $html);
    }

    public function test_the_bulk_action_column_width_can_be_overridden_per_table(): void
    {
        $html = $this->renderTableWithBulkActions('12rem');

        $this->assertStringContainsString('style="width: 12rem;"', $html);
        $this->assertStringNotContainsString('width: 5%', $html);
    }

    public function test_a_null_bulk_action_column_width_omits_the_inline_style(): void
    {
        $html = $this->renderTableWithBulkActions(null, true);

        $this->assertStringNotContainsString('style="width:', $html);
    }

    private function renderTableWithBulkActions(?string $width = null, bool $overrideWithNull = false): string
    {
        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $table = new class($width, $overrideWithNull) extends Table {
            public function __construct(
                private readonly ?string $width,
                private readonly bool $overrideWithNull,
            ) {}

            public function columns(): array
            {
                return [new Column('name')];
            }

            public function query(): Builder
            {
                return TestModel::query();
            }

            public function bulkActions(): array
            {
                return [new Action()->label('Delete')->as(new Http('https://example.test', Method::Delete))];
            }

            public function bulkActionColumnWidth(): ?string
            {
                if ($this->overrideWithNull) {
                    return null;
                }

                return $this->width ?? parent::bulkActionColumnWidth();
            }
        };

        return $builder->build($table, $request)->render();
    }
}
