<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Collections;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\Enums\ActionCollectionType;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

/**
 * @extends Collection<int, Action|ActionCollection>
 */
class ActionCollection extends Collection
{
    public protected(set) ActionCollectionType $type = ActionCollectionType::Normal {
        get => $this->type;
    }

    /**
     * @var null|\Closure(ActionContext): string|string
     */
    public protected(set) \Closure|string|null $label = null {
        get => $this->label;
    }

    public protected(set) ?StyleSet $style = null {
        get => $this->style;
    }

    /**
     * @param array<Action|ActionCollection> $items
     */
    public function __construct(array $items = [], ?ActionCollectionType $type = null)
    // @mago-expect analysis:less-specific-argument -- Collection's template is invariant, so the narrower item type is rejected
    {
        parent::__construct($items);

        $this->type = $type ?? ActionCollectionType::Normal;
    }

    public function type(ActionCollectionType $type): static
    {
        $clone       = clone $this;
        $clone->type = $type;

        return $clone;
    }

    /**
     * @param \Closure(ActionContext $context): string|string $label
     */
    public function label(\Closure|string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function style(ButtonStyle|\Closure ...$styles): static
    {
        $this->style = $this->style?->with(...$styles) ?? new StyleSet(...$styles);

        return $this;
    }

    public function countRenderable(ActionContext $context): int
    {
        return $this->sum(static function (Action|ActionCollection $item) use ($context) {
            if ($item instanceof ActionCollection) {
                return $item->countRenderable($context);
            }

            return $item->hasDescriptor($context) ? 1 : 0;
        });
    }

    public function hasRenderable(ActionContext $context): bool
    {
        return $this->countRenderable($context) > 0;
    }

    public function normal(Action ...$actions): ActionCollection
    {
        return $this->asType(ActionCollectionType::Normal, $actions);
    }

    public function group(Action ...$actions): ActionCollection
    {
        return $this->asType(ActionCollectionType::Grouped, $actions);
    }

    public function dropdown(Action ...$actions): ActionCollection
    {
        return $this->asType(ActionCollectionType::Dropdown, $actions);
    }

    /**
     * Returns a copy of this collection under the given type, with the given actions appended.
     *
     * The actions already in the collection are kept, so `new ActionCollection([$a])->dropdown()` turns that
     * collection into a dropdown instead of silently replacing it with an empty one.
     *
     * @param array<array-key, Action> $actions
     */
    private function asType(ActionCollectionType $type, array $actions): static
    {
        $clone       = clone $this;
        $clone->type = $type;

        foreach ($actions as $action) {
            $clone->push($action);
        }

        return $clone;
    }
}
