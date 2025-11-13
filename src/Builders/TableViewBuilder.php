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

readonly class TableViewBuilder
{
    public function __construct(
        private Factory $viewFactory,
        private Repository $config,
        private ColumnLabelViewBuilder $columnLabelViewBuilder,
        private ColumnValueViewBuilder $columnValueViewBuilder,
    ) {}

    public function build(Table $table, Request $request): View
    {
        $theme = $this->config->get('eloquent-tables.theme', Theme::Bootstrap5);

        return $this->viewFactory->make('eloquent-tables::table', [
            'theme'       => $theme,
            'request'     => $request,
            'tableStyles' => collect($table->tableStyles())
                ->each(fn (TableStyle $style) => $style->toCssClass($theme))
                ->implode(' '),
        ]);
    }
}
