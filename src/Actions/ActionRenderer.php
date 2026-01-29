<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use Illuminate\Contracts\View\View;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\Collections\ActionCollection;

final readonly class ActionRenderer
{
    public function __construct(
        private Config $config,
    ) {}

    public function render(Action|ActionCollection $action, ActionContext $context): ?View
    {
        return $action instanceof ActionCollection
            ? $this->renderActionCollection($action, $context)
            : $this->renderAction($action, $context);
    }

    public function canRender(Action|ActionCollection $action, ActionContext $context): bool
    {
        return $action instanceof ActionCollection
            ? $action->hasRenderable($context)
            : $action->hasDescriptor($context);
    }

    private function renderActionCollection(ActionCollection $collection, ActionContext $context): ?View
    {
        return $this->canRender($collection, $context) ? view($collection->type->view(), [
            'actions'        => $collection,
            'context'        => $context,
            'theme'          => $this->config->theme(),
            'dataNamespace'  => $this->config->dataNamespace(),
            'label'          => new LazyValue($collection->label)->resolve($context),
            'actionRenderer' => $this,
        ]) : null;
    }

    private function renderAction(Action $action, ActionContext $context): ?View
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
            'id'                 => md5(uniqid(more_entropy: true)),
        ]);

        // Call after render hook
        $descriptor->intent?->afterRender($descriptor, $context);

        return $result;
    }
}
