<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Builders;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Actions\RowAction;
use BrickNPC\EloquentTables\Enums\ButtonStyle;

readonly class RowActionBuilder
{
    public function __construct(
        private Factory $viewFactory,
        private Config $config,
    ) {}

    public function build(RowAction $action, Request $request, Model $model): ?View
    {
        if (!$this->isAuthorized($action, $request, $model)) {
            return null;
        }

        if (!$this->when($action, $model)) {
            return null;
        }

        return $this->viewFactory->make('eloquent-tables::action.row-action', [
            'id'            => $this->getId($action, $model),
            'theme'         => $this->config->theme(),
            'dataNamespace' => $this->config->dataNamespace(),
            'action'        => is_string($action->action) ? $action->action : call_user_func($action->action, $model),
            'styles'        => collect($action->styles)->map(fn (ButtonStyle $style) => $style->toCssClass($this->config->theme()))->implode(' '),
            'label'         => $action->label,
            'confirm'       => $action->confirm,
            'confirmValue'  => $action->confirmValue,
            'asForm'        => $action->asForm,
            'method'        => $action->method,
        ]);
    }

    private function isAuthorized(RowAction $action, Request $request, Model $model): bool
    {
        if (null === $action->authorize) {
            return true;
        }

        return (bool) call_user_func($action->authorize, $request, $model);
    }

    private function when(RowAction $action, Model $model): bool
    {
        if (null === $action->when) {
            return true;
        }

        return (bool) call_user_func($action->when, $model);
    }

    private function getId(RowAction $action, Model $model): string
    {
        $objectId = (string) spl_object_id($action);
        $modelId  = (string) spl_object_id($model);
        $random   = uniqid();

        return sprintf('%s-%s-%s', $objectId, $modelId, $random);
    }
}
