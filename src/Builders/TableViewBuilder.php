<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use BrickNPC\EloquentTables\Enums\Theme;
use Illuminate\Contracts\Config\Repository;
use BrickNPC\EloquentTables\Enums\TableStyle;
use BrickNPC\EloquentTables\Services\LayoutFinder;

readonly class TableViewBuilder
{
    public function __construct(
        private Factory $viewFactory,
        private Repository $config,
        private ColumnLabelViewBuilder $columnLabelViewBuilder,
        private ColumnValueViewBuilder $columnValueViewBuilder,
        private LayoutFinder $layoutFinder,
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

    private function getViewData(Table $table, Request $request): array
    {
        $theme = $this->config->get('eloquent-tables.theme', Theme::Bootstrap5);

        $viewData = [
            'theme'       => $theme,
            'request'     => $request,
            'tableStyles' => collect($table->tableStyles())
                ->each(fn (TableStyle $style) => $style->toCssClass($theme))
                ->implode(' '),
        ];

        $layout = $this->layoutFinder->getLayout($table);
        if (null !== $layout) {
            $viewData['layout'] = $layout;
        }

        return $viewData;
    }
}
