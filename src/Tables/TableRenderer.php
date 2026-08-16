<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tables;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Column;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Htmlable;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Enums\CellStyle;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Contracts\Filter;
use BrickNPC\EloquentTables\Enums\StyleTarget;
use BrickNPC\EloquentTables\Builders\RowsBuilder;
use BrickNPC\EloquentTables\Enums\TableParameter;
use BrickNPC\EloquentTables\Styles\StyleResolver;
use BrickNPC\EloquentTables\Services\LayoutFinder;
use BrickNPC\EloquentTables\Actions\ActionRenderer;
use BrickNPC\EloquentTables\Filters\FilterRenderer;
use BrickNPC\EloquentTables\Concerns\WithPagination;
use BrickNPC\EloquentTables\Services\TableParameters;
use BrickNPC\EloquentTables\Services\RouteModelBinder;
use BrickNPC\EloquentTables\Styles\Contexts\RowContext;
use BrickNPC\EloquentTables\Columns\ColumnLabelRenderer;
use BrickNPC\EloquentTables\Columns\ColumnValueRenderer;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\Collections\ActionCollection;

/**
 * @template TModel of Model
 *
 * <todo>
 * All the ignore comments for PHPStan in this class are because of the fact that the WithPagination trait is
 * optional for tables. PHPStan does not have a valid way to handle this, so instead of writing a bunch of
 * unnecessary code I choose to ignore PHPStan in this regard, even though it is not best practice.
 *
 * If a better solution exists or is implemented in the future, feel free to open a PR.
 * </todo>
 */
readonly class TableRenderer
{
    /**
     * @param ColumnLabelRenderer<TModel> $columnLabelRenderer
     * @param ColumnValueRenderer<TModel> $columnValueRenderer
     * @param LayoutFinder<TModel>        $layoutFinder
     * @param RowsBuilder<TModel>         $rowsBuilder
     * @param FilterRenderer<TModel>      $filterRenderer
     * @param TableParameters<TModel>     $parameters
     */
    public function __construct(
        private ColumnLabelRenderer $columnLabelRenderer,
        private ColumnValueRenderer $columnValueRenderer,
        private Factory $viewFactory,
        private LayoutFinder $layoutFinder,
        private Config $config,
        private RowsBuilder $rowsBuilder,
        private FilterRenderer $filterRenderer,
        private RouteModelBinder $methodInvoker,
        private ActionRenderer $actionRenderer,
        private TableParameters $parameters,
        private StyleResolver $styleResolver,
    ) {}

    /**
     * @param Table<TModel> $table
     */
    public function build(Table $table, Request $request): View
    {
        return $this->viewFactory->make(
            $this->getViewFile($table),
            $this->getViewData($table, $request),
        );
    }

    /**
     * @param Table<TModel> $table
     */
    private function getViewFile(Table $table): string
    {
        $layout = $this->layoutFinder->getLayout($table);

        return match ($layout) {
            null    => 'eloquent-tables::table',
            default => 'eloquent-tables::table-with-layout',
        };
    }

    /**
     * @param Table<TModel> $table
     *
     * @return array<string, mixed>
     */
    private function getViewData(Table $table, Request $request): array
    {
        $theme = $this->config->theme();

        /** @var Column<TModel>[] $columns */
        $columns = $this->methodInvoker->call($table, 'columns');

        /** @var Filter[] $filters */
        $filters = $table->hasFilters() ? $this->methodInvoker->call($table, 'filters') : [];

        $tableActions = method_exists($table, 'tableActions') ? $this->methodInvoker->call($table, 'tableActions') : [];

        /** @var Action[]|ActionCollection[] $tableActions */
        $tableActions = is_array($tableActions) ? $tableActions : [$tableActions];

        $rowActions = method_exists($table, 'rowActions') ? $this->methodInvoker->call($table, 'rowActions') : [];

        /** @var Action[]|ActionCollection[] $rowActions */
        $rowActions = is_array($rowActions) ? $rowActions : [$rowActions];

        $bulkActions = method_exists($table, 'bulkActions') ? $this->methodInvoker->call($table, 'bulkActions') : [];

        /** @var Action[]|ActionCollection[] $bulkActions */
        $bulkActions = is_array($bulkActions) ? $bulkActions : [$bulkActions];

        $rows = $this->getRows($table, $request);

        $context = new ActionContext($request, $this->config);

        $viewData = [
            'id'            => spl_object_id($table),
            'table'         => $table,
            'tableName'     => $table->name(),
            'preferences'   => $this->config->preferencesEnabled() ? [
                'cookie'     => $this->config->preferencesCookieName(),
                'perPageKey' => $this->parameters->key($table, TableParameter::PerPage),
                'sortKey'    => $this->parameters->key($table, TableParameter::Sort),
            ] : null,
            'theme'                  => $theme,
            'dataNamespace'          => $this->config->dataNamespace(),
            'request'                => $request,
            'tableStyles'            => $this->styleResolver->classes($table->style()),
            'columns'                => $columns,
            'columnLabelRenderer'    => $this->columnLabelRenderer,
            'rows'                   => $rows,
            'rowStyles'              => $this->rowStyles($table, $rows, $request),
            'columnValueRenderer'    => $this->columnValueRenderer,
            'links'                  => $this->getLinks($table, $request),
            'tableActionCount'       => $this->actionRenderer->countRenderable($tableActions, $context),
            'tableActions'           => $tableActions,
            'showSearchForm'         => $this->hasSearchableColumns($columns),
            'tableSearchUrl'         => $request->fullUrl(),
            'fullUrl'                => $request->fullUrl(),
            'searchQuery'            => $this->parameters->stringValue($table, TableParameter::Search, $request),
            'searchQueryName'        => $this->parameters->key($table, TableParameter::Search),
            'searchHiddenInputs'     => $this->parameters->hiddenInputs($request, [
                $this->parameters->key($table, TableParameter::Search),
                // A new search changes the result set, so the old page number no longer means anything.
                $this->parameters->key($table, TableParameter::Page),
            ]),
            'searchIcon'             => $this->config->searchIcon(),
            'rowActionCount'         => $this->countRenderableRowActions($rowActions, $rows, $request),
            'rowActions'             => $rowActions,
            'bulkActionCount'        => $this->actionRenderer->countRenderable($bulkActions, $context->isBulk()),
            'bulkActions'            => $bulkActions,
            'bulkActionColumnWidth'  => $table->bulkActionColumnWidth(),
            'bulkActionCellStyles'   => $this->styleResolver->classes([CellStyle::AlignCenter], StyleTarget::Content),
            'rowActionCellStyles'    => $this->styleResolver->classes([CellStyle::AlignRight], StyleTarget::Content),
            'filterCount'            => count($filters),
            'filters'                => $filters,
            'filterRenderer'         => $this->filterRenderer,
            'actionRenderer'         => $this->actionRenderer,
            'config'                 => $this->config,
        ];

        $layout = $this->layoutFinder->getLayout($table);
        if ($layout !== null) {
            $viewData['layout'] = $layout;
        }

        $accent = $table->accentStyle();

        $viewData['mainTableStyle'] = $accent->toCssClass($theme);
        $viewData['disabledStyle']  = $accent->toCssDisabledClass($theme);
        $viewData['activeStyle']    = $accent->toCssActiveClass($theme);

        if ($table->withPagination()) {
            /* @var WithPagination|Table $table */
            $viewData['perPage']             = $this->parameters->perPage($table, $request, $table->perPage($request)); // @phpstan-ignore-line
            $viewData['perPageName']         = $this->parameters->key($table, TableParameter::PerPage);
            $viewData['perPageOptions']      = $table->perPageOptions(); // @phpstan-ignore-line
            $viewData['perPageHiddenInputs'] = $this->parameters->hiddenInputs($request, [
                $this->parameters->key($table, TableParameter::PerPage),
                // Changing the page size returns the table to its first page.
                $this->parameters->key($table, TableParameter::Page),
            ]);
        }

        return $viewData;
    }

    /**
     * @param Table<TModel>          $table
     * @param Collection<int, Model> $rows
     *
     * @return string[]
     */
    private function rowStyles(Table $table, Collection $rows, Request $request): array
    {
        $set = $table->rowStyle();

        if ($set === null) {
            return [];
        }

        return $rows
            ->values()
            ->map(fn (Model $row) => $this->styleResolver->classes($set->resolve(new RowContext($request, $row))))
            ->all()
        ;
    }

    /**
     * @param array<Action|ActionCollection> $rowActions
     * @param Collection<int, Model>         $rows
     */
    private function countRenderableRowActions(array $rowActions, Collection $rows, Request $request): int
    {
        $count = 0;

        foreach ($rowActions as $action) {
            foreach ($rows as $row) {
                if ($this->actionRenderer->canRender($action, new ActionContext($request, $this->config, $row))) {
                    ++$count;

                    break;
                }
            }
        }

        return $count;
    }

    /**
     * @param Table<TModel> $table
     *
     * @return Collection<int, Model>
     */
    private function getRows(Table $table, Request $request): Collection
    {
        $results = $this->rowsBuilder->build($table, $request);

        return $results instanceof Collection ? $results : $results->getCollection();
    }

    /**
     * @param Table<TModel> $table
     */
    private function getLinks(Table $table, Request $request): ?Htmlable
    {
        if (!$table->withPagination()) {
            return null;
        }

        $theme = $this->config->theme();

        return $this->rowsBuilder->build($table, $request)->links($theme->getLinksView(), [ // @phpstan-ignore-line
            'mainTableStyle' => $table->accentStyle()->toCssClass($theme),
            'disabledStyle'  => $table->accentStyle()->toCssDisabledClass($theme),
            'activeStyle'    => $table->accentStyle()->toCssActiveClass($theme),
        ]);
    }

    /**
     * @param Column<TModel>[] $columns
     */
    private function hasSearchableColumns(array $columns): bool
    {
        return collect($columns)
            ->filter(fn (Column $column) => $column->searchable)
            ->isNotEmpty()
        ;
    }
}
