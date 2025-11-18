<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Builders;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\MassAction;

readonly class MassActionViewBuilder
{
    public function __construct(
        private Factory $viewFactory,
        private Config $config,
    ) {}

    public function build(MassAction $action, Request $request): ?View
    {
        if (!$this->isAuthorized($action, $request)) {
            return null;
        }

        return $this->viewFactory->make('eloquent-tables::action.mass-action', [
            'theme'         => $this->config->theme(),
            'dataNamespace' => $this->config->dataNamespace(),
            'id'            => $this->getId($action),
            'action'        => $action->action,
            'styles'        => collect($action->styles)->map(fn (ButtonStyle $style) => $style->toCssClass($this->config->theme()))->implode(' '),
            'label'         => $action->label,
            'method'        => $action->method,
            'confirm'       => $action->confirm,
            'confirmValue'  => $action->confirmValue,
            'tooltip'       => $action->tooltip,
        ]);
    }

    private function getId(MassAction $action): string
    {
        $objectId = (string) spl_object_id($action);
        $random   = uniqid();

        return sprintf('%s-%s', $objectId, $random);
    }

    private function isAuthorized(MassAction $action, Request $request): bool
    {
        if (null === $action->authorize) {
            return true;
        }

        return (bool) call_user_func($action->authorize, $request);
    }
}
