<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use Illuminate\Contracts\View\View;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\ActionRegion;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Styles\ActionStyleResolver;
use BrickNPC\EloquentTables\Exceptions\ActionIntentNotSet;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\Collections\ActionCollection;

final readonly class ActionRenderer
{
    public function __construct(
        private Config $config,
        private ActionStyleResolver $styles = new ActionStyleResolver(),
    ) {}

    /**
     * @throws ActionIntentNotSet
     */
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

    /**
     * @param array<Action|ActionCollection> $actions
     */
    public function countRenderable(array $actions, ActionContext $context): int
    {
        return count(array_filter(
            $actions,
            fn (Action|ActionCollection $action) => $this->canRender($action, $context),
        ));
    }

    private function renderActionCollection(ActionCollection $collection, ActionContext $context): ?View
    {
        return $this->canRender($collection, $context) ? view($collection->type->view(), [
            'actions'        => $collection,
            'context'        => $context,
            'theme'          => $this->config->theme(),
            'dataNamespace'  => $this->config->dataNamespace(),
            'label'          => new LazyValue($collection->label)->resolve($context),
            'toggleClasses'  => $this->styles->classes($collection->style, $context, ActionRegion::DropdownToggle),
            'actionRenderer' => $this,
        ]) : null;
    }

    /**
     * @throws ActionIntentNotSet
     */
    private function renderAction(Action $action, ActionContext $context): ?View
    {
        $descriptor = $action->descriptor($context);

        if ($descriptor === null) {
            return null;
        }

        $intent = $descriptor->intent;

        if ($intent === null) {
            throw ActionIntentNotSet::forAction($action);
        }

        // Call before render hook
        $intent->beforeRender($descriptor, $context);

        $region = $context->asDropdown ? ActionRegion::DropdownItem : ActionRegion::Button;

        $result = view($intent->view(), [
            'theme'              => $this->config->theme(),
            'dataNamespace'      => $this->config->dataNamespace(),
            'context'            => $context,
            'label'              => $descriptor->label->resolve($context),
            'attributes'         => $descriptor->attributes,
            'classes'            => $this->styles->classes($descriptor->style, $context, $region),
            'beforeContent'      => $descriptor->beforeRender,
            'afterContent'       => $descriptor->afterRender,
            'renderedAttributes' => $descriptor->attributesRender,
            'intent'             => $intent,
            'id'                 => md5(uniqid(more_entropy: true)),
        ]);

        // Call after render hook
        $intent->afterRender($descriptor, $context);

        return $result;
    }
}
