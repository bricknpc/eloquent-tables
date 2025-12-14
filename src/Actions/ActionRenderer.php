<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

final readonly class ActionRenderer
{
    public function __construct(
        private Config $config,
    ) {}

    /**
     * @template TModel of Model
     *
     * @param ActionContext<TModel> $context
     */
    public function render(Action $action, ActionContext $context): ?View
    {
        $descriptor = $action->descriptor($context);

        if ($descriptor === null) {
            return null;
        }

        // Call before render hook
        $descriptor->intent?->beforeRender($descriptor, $context);

        $result = view($descriptor->intent?->view() ?? 'eloquent-tables::actions.default', [
            'theme'              => $this->config->theme(),
            'dataNamespace'      => $this->config->dataNamespace(),
            'context'            => $context,
            'label'              => $descriptor->label->resolve($context),
            'attributes'         => $descriptor->attributes,
            'beforeContent'      => $descriptor->beforeRender,
            'afterContent'       => $descriptor->afterRender,
            'renderedAttributes' => $descriptor->attributesRender,
            'intent'             => $descriptor->intent,
        ]);

        // Call after render hook
        $descriptor->intent?->afterRender($descriptor, $context);

        return $result;
    }
}
