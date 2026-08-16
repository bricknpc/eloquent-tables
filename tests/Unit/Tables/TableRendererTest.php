<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Tables;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use BrickNPC\EloquentTables\Column;
use Illuminate\Contracts\View\View;
use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Enums\RowStyle;
use BrickNPC\EloquentTables\Filters\Filter;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Enums\CellStyle;
use BrickNPC\EloquentTables\Enums\PageStyle;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\ColumnType;
use BrickNPC\EloquentTables\Enums\TableStyle;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Attributes\Layout;
use BrickNPC\EloquentTables\Enums\StyleFamily;
use BrickNPC\EloquentTables\Enums\StyleTarget;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\Actions\Intents\Http;
use BrickNPC\EloquentTables\Builders\RowsBuilder;
use BrickNPC\EloquentTables\Styles\StyleResolver;
use BrickNPC\EloquentTables\Tables\TableRenderer;
use BrickNPC\EloquentTables\Services\LayoutFinder;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\Actions\ActionRenderer;
use BrickNPC\EloquentTables\Filters\FilterRenderer;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Concerns\WithPagination;
use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Services\TableParameters;
use BrickNPC\EloquentTables\Actions\Capabilities\When;
use BrickNPC\EloquentTables\Services\RouteModelBinder;
use BrickNPC\EloquentTables\Services\TablePreferences;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Tests\Resources\TestTable;
use BrickNPC\EloquentTables\Factories\FormatterFactory;
use BrickNPC\EloquentTables\Styles\Contexts\RowContext;
use BrickNPC\EloquentTables\Columns\ColumnLabelRenderer;
use BrickNPC\EloquentTables\Columns\ColumnValueRenderer;
use BrickNPC\EloquentTables\Styles\Contexts\TableContext;
use BrickNPC\EloquentTables\Actions\Capabilities\Authorize;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer;
use BrickNPC\EloquentTables\Actions\Collections\ActionCollection;

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
#[UsesClass(TableParameters::class)]
#[UsesClass(TablePreferences::class)]
#[UsesClass(RowsBuilder::class)]
#[UsesClass(FilterRenderer::class)]
#[UsesClass(Filter::class)]
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
#[UsesClass(Authorize::class)]
#[UsesClass(When::class)]
#[UsesClass(ActionCapability::class)]
#[UsesClass(ColumnType::class)]
#[UsesClass(ActionCollection::class)]
#[UsesClass(StyleResolver::class)]
#[UsesClass(CellStyle::class)]
#[UsesClass(StyleTarget::class)]
#[UsesClass(StyleFamily::class)]
#[UsesClass(RowStyle::class)]
#[UsesClass(RowContext::class)]
#[UsesClass(StyleSet::class)]
#[UsesClass(TableContext::class)]
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
        $this->assertSame('table[per_page]', $viewData['perPageName']);

        $this->assertArrayHasKey('perPageOptions', $viewData);
        $this->assertSame([10, 25, 50], $viewData['perPageOptions']);
    }

    public function test_the_per_page_control_keeps_the_rest_of_the_table_state(): void
    {
        // Covers AE6.
        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        $table = new class extends Table {
            use WithPagination;

            protected array $perPageOptions = [10, 25];

            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [
                    new Column('name')->sortable()->searchable(),
                ];
            }

            public function filters(): array
            {
                return [
                    new Filter('name', []),
                ];
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('table', [
            'search' => 'ada',
            'sort'   => ['name' => 'asc'],
            'filter' => ['name' => 'Test Model 01'],
            'page'   => '3',
        ]);
        $request->query->set('orders', ['sort' => ['total' => 'desc']]);

        $html = $builder->build($table, $request)->render();

        $this->assertStringContainsString('<input type="hidden" name="table[search]" value="ada"/>', $html);
        $this->assertStringContainsString('<input type="hidden" name="table[sort][name]" value="asc"/>', $html);
        $this->assertStringContainsString('<input type="hidden" name="table[filter][name]" value="Test Model 01"/>', $html);
        $this->assertStringContainsString('<input type="hidden" name="orders[sort][total]" value="desc"/>', $html);

        // The page is deliberately dropped so a new page size starts from the first page.
        $this->assertStringNotContainsString('name="table[page]"', $html);
    }

    public function test_the_table_publishes_its_name_to_the_browser(): void
    {
        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $html = $builder->build(new TestTable(), $request)->render();

        $this->assertStringContainsString('data-et-table-name="test"', $html);
        // The existing hook keeps carrying the per-render id; it is a published contract.
        $this->assertStringContainsString('data-et-table="', $html);
    }

    public function test_it_warns_the_browser_about_duplicate_table_names(): void
    {
        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $html = $builder->build(new TestTable(), $request)->render();

        $this->assertStringContainsString('data-et-table-name', $html);
        $this->assertStringContainsString('are named', $html);
    }

    public function test_it_publishes_the_preference_hooks_to_the_browser(): void
    {
        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $html = $builder->build(new TestTable(), $request)->render();

        $this->assertStringContainsString('data-et-preferences-cookie="eloquent_tables_preferences"', $html);
        $this->assertStringContainsString('data-et-preferences-per-page-key="test[per_page]"', $html);
        $this->assertStringContainsString('data-et-preferences-sort-key="test[sort]"', $html);
    }

    public function test_it_publishes_no_preference_hooks_when_preferences_are_disabled(): void
    {
        // Covers AE8: with preferences off, nothing is written to the visitor's device.
        config()->set('eloquent-tables.preferences.enabled', false);

        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $html = $builder->build(new TestTable(), $request)->render();

        // The bundled script always renders and names these attributes, so assert on the attribute
        // form rather than the bare name.
        $this->assertStringNotContainsString('data-et-preferences-cookie="', $html);
        $this->assertStringNotContainsString('data-et-preferences-sort-key="', $html);
    }

    public function test_it_uses_the_configured_preferences_cookie_name(): void
    {
        config()->set('eloquent-tables.preferences.cookie_name', 'prefs');

        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $html = $builder->build(new TestTable(), $request)->render();

        $this->assertStringContainsString('data-et-preferences-cookie="prefs"', $html);
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

    public function test_actions_that_can_not_render_are_not_counted(): void
    {
        $view = $this->buildActionTable(allow: false);

        $data = $view->getData();

        $this->assertSame(0, $data['tableActionCount']);
        $this->assertSame(0, $data['rowActionCount']);
        $this->assertSame(0, $data['bulkActionCount']);
    }

    public function test_nothing_is_rendered_around_actions_that_can_not_render(): void
    {
        $html = $this->buildActionTable(allow: false)->render();

        $this->assertStringNotContainsString('table-actions me-3', $html);
        $this->assertStringNotContainsString('table-bulk-actions', $html);
        $this->assertStringNotContainsString('<th>&nbsp;</th>', $html);
        $this->assertStringNotContainsString('<div class="btn-group">', $html);
        $this->assertStringNotContainsString('type="checkbox" name="selected[]"', $html);
    }

    public function test_actions_that_can_render_are_counted_and_rendered(): void
    {
        $view = $this->buildActionTable(allow: true);

        $data = $view->getData();
        $html = $view->render();

        $this->assertSame(1, $data['tableActionCount']);
        $this->assertSame(1, $data['rowActionCount']);
        $this->assertSame(1, $data['bulkActionCount']);

        $this->assertStringContainsString('table-actions me-3', $html);
        $this->assertStringContainsString('table-bulk-actions', $html);
        $this->assertStringContainsString('<th>&nbsp;</th>', $html);

        // One button group per row.
        $this->assertSame(2, substr_count($html, '<div class="btn-group">'));
    }

    public function test_the_row_action_column_survives_rows_that_have_no_renderable_action(): void
    {
        $view = $this->buildActionTable(
            allow: true,
            rowCondition: fn (ActionContext $context) => $context->model->name === 'A',
        );

        $html = $view->render();

        // The column stays, because row A needs it...
        $this->assertSame(1, $view->getData()['rowActionCount']);
        $this->assertStringContainsString('<th>&nbsp;</th>', $html);

        // ...but only row A gets a button group. Row B keeps an empty cell so the table stays aligned.
        $this->assertSame(1, substr_count($html, '<div class="btn-group">'));
        $this->assertSame(2, substr_count($html, $view->getData()['rowActionCellStyles']));
    }

    public function test_a_collection_counts_as_a_single_action(): void
    {
        $view = $this->buildActionTable(allow: true, groupRowActions: true);

        $this->assertSame(1, $view->getData()['rowActionCount']);
    }

    public function test_a_conditional_row_style_lands_only_on_matching_rows(): void
    {
        // Covers AE7.
        $html = $this->renderStyledRows(new StyleSet(
            fn (RowContext $context) => $context->model->name === 'A' ? RowStyle::Danger : null,
        ));

        $this->assertSame(1, substr_count($html, '<tr class="table-danger">'));
        $this->assertSame(1, substr_count($html, '<tr class="">'));
    }

    public function test_a_static_row_style_applies_to_every_row(): void
    {
        $html = $this->renderStyledRows(new StyleSet(RowStyle::Info));

        $this->assertSame(2, substr_count($html, '<tr class="table-info">'));
    }

    public function test_a_table_without_row_styles_renders_rows_without_a_class(): void
    {
        $html = $this->renderStyledRows(null);

        $this->assertSame(2, substr_count($html, '<tr class="">'));
        $this->assertStringNotContainsString('table-danger', $html);
    }

    public function test_a_row_style_covers_the_cells_that_columns_cannot_reach(): void
    {
        // Covers AE7. The checkbox and action cells sit outside the column loop, so only a row-level
        // style reaches them.
        $html = $this->renderStyledRows(new StyleSet(RowStyle::Danger), withActions: true);

        $styled = substr($html, strpos($html, '<tr class="table-danger">'));
        $styled = substr($styled, 0, strpos($styled, '</tr>'));

        $this->assertStringContainsString('name="selected[]"', $styled);
        $this->assertStringContainsString('btn-group', $styled);
    }

    public function test_the_row_closure_receives_its_own_row(): void
    {
        $seen = [];

        $this->renderStyledRows(new StyleSet(function (RowContext $context) use (&$seen) {
            $seen[] = $context->model->name;

            return null;
        }));

        $this->assertSame(['A', 'B'], $seen);
    }

    public function test_a_declared_table_style_reaches_the_table_element(): void
    {
        $html = $this->renderStyledTable(new StyleSet(TableStyle::Striped, TableStyle::Hover));

        $this->assertStringContainsString('<table class="table table-striped table-hover">', $html);
    }

    public function test_a_table_declaring_nothing_keeps_its_default_appearance(): void
    {
        $this->assertStringContainsString('<table class="table ">', $this->renderStyledTable(null));
    }

    public function test_a_conditional_table_style_resolves_against_the_table_context(): void
    {
        $seen = null;

        $html = $this->renderStyledTable(new StyleSet(function (TableContext $context) use (&$seen) {
            $seen = $context;

            return TableStyle::Bordered;
        }));

        $this->assertInstanceOf(TableContext::class, $seen);
        $this->assertStringContainsString('table-bordered', $html);
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

    private function renderStyledTable(?StyleSet $style): string
    {
        $table = new class($style) extends Table {
            public function __construct(private readonly ?StyleSet $tableStyle) {}

            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [new Column('name')];
            }

            public function style(): ?StyleSet
            {
                return $this->tableStyle;
            }
        };

        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        return $builder->build($table, $request)->render();
    }

    private function renderStyledRows(?StyleSet $rowStyle, bool $withActions = false): string
    {
        DB::table('test_models')->insert([
            ['name' => 'A', 'email' => 'a@test.com', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'B', 'email' => 'b@test.com', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $table = new class($rowStyle, $withActions) extends Table {
            public function __construct(
                private readonly ?StyleSet $rowStyle,
                private readonly bool $withActions,
            ) {}

            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [new Column('name')];
            }

            public function rowStyle(): ?StyleSet
            {
                return $this->rowStyle;
            }

            public function rowActions(): array
            {
                return $this->withActions
                    ? [new Action()->label('R')->as(new Http('https://example.test'))]
                    : [];
            }

            public function bulkActions(): array
            {
                return $this->withActions
                    ? [new Action()->label('B')->as(new Http('https://example.test'))]
                    : [];
            }
        };

        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        return $builder->build($table, $request)->render();
    }

    private function buildActionTable(bool $allow, ?\Closure $rowCondition = null, bool $groupRowActions = false): View
    {
        DB::table('test_models')->insert([
            ['name' => 'A', 'email' => 'a@test.com', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'B', 'email' => 'b@test.com', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $table = new class($allow, $rowCondition, $groupRowActions) extends Table {
            public function __construct(
                private readonly bool $allow,
                private readonly ?\Closure $rowCondition,
                private readonly bool $groupRowActions,
            ) {}

            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [new Column('name')];
            }

            public function tableActions(): array
            {
                return [$this->action('T')];
            }

            public function rowActions(): array
            {
                $action = $this->rowCondition !== null
                    ? new Action()->label('R')->as(new Http('https://example.test'))->with(new When($this->rowCondition))
                    : $this->action('R');

                return $this->groupRowActions
                    ? [new ActionCollection()->group($action, $this->action('R2'))]
                    : [$action];
            }

            public function bulkActions(): array
            {
                return [$this->action('B')];
            }

            private function action(string $label): Action
            {
                return new Action()
                    ->label($label)
                    ->as(new Http('https://example.test'))
                    ->with(new Authorize(fn () => $this->allow))
                ;
            }
        };

        /** @var TableRenderer $builder */
        $builder = $this->app->make(TableRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        return $builder->build($table, $request);
    }
}
