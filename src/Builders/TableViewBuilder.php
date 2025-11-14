<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\Theme;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Config\Repository;
use BrickNPC\EloquentTables\Enums\TableStyle;
use Illuminate\Contracts\Pagination\Paginator;
use BrickNPC\EloquentTables\Services\LayoutFinder;
use BrickNPC\EloquentTables\Concerns\WithPagination;

/**
 * <todo>
 * All the ignore comments for PHPStan in this class are because of the fact that the WithPagination trait is
 * optional for tables. PHPStan does not have a valid way to handle this, so instead of writing a bunch of
 * unnecessary code I choose to ignore PHPStan in this regard, even though it is not best practice.
 *
 * If a better solution exists or is implemented in the future, feel free to open a PR.
 * </todo>
 */
class TableViewBuilder
{
    /**
     * @var null|Collection<int, Model>|Paginator<int, Model>
     */
    private Collection|Paginator|null $results = null;

    public function __construct(
        private readonly Factory $viewFactory,
        private readonly Repository $config,
        private readonly ColumnLabelViewBuilder $columnLabelViewBuilder,
        private readonly ColumnValueViewBuilder $columnValueViewBuilder,
        private readonly LayoutFinder $layoutFinder,
    ) {}

    public function build(Table $table, Request $request): View
    {
        return $this->viewFactory->make(
            $this->getViewFile($table),
            $this->getViewData($table, $request),
        );
    }

    private function getViewFile(Table $table): string
    {
        $layout = $this->layoutFinder->getLayout($table);

        return match ($layout) {
            null    => 'eloquent-tables::table',
            default => 'eloquent-tables::table-with-layout',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function getViewData(Table $table, Request $request): array
    {
        $theme = $this->getTheme();

        $viewData = [
            'id'          => spl_object_id($table),
            'theme'       => $theme,
            'request'     => $request,
            'tableStyles' => collect($table->tableStyles())
                ->map(fn (TableStyle $style) => $style->toCssClass($theme))
                ->implode(' '),
            'columns'                => $table->columns(),
            'columnLabelViewBuilder' => $this->columnLabelViewBuilder,
            'rows'                   => $this->getRows($table, $request),
            'columnValueViewBuilder' => $this->columnValueViewBuilder,
            'links'                  => $this->getLinks($table, $request),
        ];

        $layout = $this->layoutFinder->getLayout($table);
        if (null !== $layout) {
            $viewData['layout'] = $layout;
        }

        return $viewData;
    }

    /**
     * @param Table|WithPagination $table
     *
     * @return Collection<int, Model>
     */
    private function getRows(Table $table, Request $request): Collection // @phpstan-ignore-line
    {
        $results = $this->getResults($table, $request);

        return $table->withPagination() ? $results->values() : $results; // @phpstan-ignore-line
    }

    /**
     * @param Table|WithPagination $table
     */
    private function getLinks(Table $table, Request $request): ?Htmlable // @phpstan-ignore-line
    {
        if (!$table->withPagination()) {
            return null;
        }

        $theme = $this->getTheme();

        return $this->getResults($table, $request)->links($theme->getLinksView()); // @phpstan-ignore-line
    }

    /**
     * @param Table|WithPagination $table
     *
     * @return Collection<int, Model>|Paginator<int, Model>
     */
    private function getResults(Table $table, Request $request): Collection|Paginator // @phpstan-ignore-line
    {
        $this->results ??= $table->withPagination() // @phpstan-ignore-line
            ? $table->query()->paginate($table->getPerPage($request), $table->perPageName)->withQueryString() // @phpstan-ignore-line
            : $table->query()->get();

        return $this->results; // @phpstan-ignore-line
    }

    private function getTheme(): Theme
    {
        /** @var Theme $theme */
        $theme = $this->config->get('eloquent-tables.theme', Theme::Bootstrap5);

        return $theme;
    }
}
