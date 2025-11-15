<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Builders;

use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\TableAction;

readonly class TableActionViewBuilder
{
    public function __construct(
        private Factory $viewFactory,
        private Config $config,
    ) {}

    public function build(TableAction $action): View
    {
        return $this->viewFactory->make('eloquent-tables::action.table-action', [
            'theme'   => $this->config->theme(),
            'action'  => $action->action,
            'styles'  => collect($action->styles)->map(fn (ButtonStyle $style) => $style->toCssClass($this->config->theme()))->implode(' '),
            'label'   => $action->label,
            'asModal' => $action->asModal,
        ]);
    }
}
