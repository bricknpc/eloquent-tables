<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Collections;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Actions\Action;
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

    /**
     * @param array<Action|ActionCollection> $items
     */
    public function __construct(array $items = [], ?ActionCollectionType $type = null)
    {
        parent::__construct($items); // @phpstan-ignore-line

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

    public function countRenderable(ActionContext $context): int
    {
        return $this->sum(function (Action|ActionCollection $item) use ($context) {
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

    public function flatten($depth = PHP_INT_MAX): static
    {
        // Custom flatten that handles nested ActionCollections
        $result = new static(); // @phpstan-ignore-line

        foreach ($this->items as $item) {
            if ($item instanceof ActionCollection) {
                $result = $result->merge($item->flatten($depth));
            } else {
                $result->push($item);
            }
        }

        return $result;
    }

    public function nest(ActionCollection $collection): static
    {
        $this->push($collection);

        return $this;
    }

    public function normal(Action ...$actions): ActionCollection
    {
        /* @var array<int, Action> $actions */
        return new ActionCollection($actions, ActionCollectionType::Normal);
    }

    public function group(Action ...$actions): ActionCollection
    {
        /* @var array<int, Action> $actions */
        return new ActionCollection($actions, ActionCollectionType::Grouped);
    }

    public function dropdown(Action ...$actions): ActionCollection
    {
        /* @var array<int, Action> $actions */
        return new ActionCollection($actions, ActionCollectionType::Dropdown);
    }
}
