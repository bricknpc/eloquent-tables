<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Builders;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\TableAction;

/**
 * @template TModel of Model
 */
readonly class TableActionViewBuilder
{
    public function __construct(
        private Factory $viewFactory,
        private Config $config,
    ) {}

    /**
     * @param TableAction<TModel> $action
     */
    public function build(TableAction $action, Request $request): ?View
    {
        if (!$this->isAuthorized($action, $request)) {
            return null;
        }

        return $this->viewFactory->make('eloquent-tables::action.table-action', [
            'theme'   => $this->config->theme(),
            'action'  => $action->action,
            'styles'  => collect($action->styles)->map(fn (ButtonStyle $style) => $style->toCssClass($this->config->theme()))->implode(' '),
            'label'   => $action->label,
            'asModal' => $action->asModal,
            'tooltip' => $action->tooltip,
        ]);
    }

    /**
     * @param TableAction<TModel> $action
     */
    private function isAuthorized(TableAction $action, Request $request): bool
    {
        if ($action->authorize === null) {
            return true;
        }

        return (bool) call_user_func($action->authorize, $request, null);
    }
}
