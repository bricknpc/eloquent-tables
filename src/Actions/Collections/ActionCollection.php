<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Collections;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Enums\ActionCollectionType;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

/**
 * @extends Collection<Action>
 */
class ActionCollection extends Collection
{
    public protected(set) ActionCollectionType $type = ActionCollectionType::Normal {
        get => $this->type;
    }

    public function __construct($items = [], ?ActionCollectionType $type = null)
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

    public function countRenderable(ActionContext $context): int
    {
        return $this->sum(function ($item) use ($context) {
            if ($item instanceof ActionCollection) {
                return $item->countRenderable($context);
            }

            return $item->descriptor($context) !== null ? 1 : 0; // @todo Calling the entire descriptor is expensive
        });
    }

    /**
     * @todo Don't like this name
     */
    public function isRenderable(ActionContext $context): bool
    {
        return $this->countRenderable($context) > 0;
    }

    public function flatten($depth = INF): self
    {
        // Custom flatten that handles nested ActionCollections
        $result = new static();

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

    public function group(Action ...$actions): static
    {
        return $this->nest(new ActionCollection($actions, ActionCollectionType::Grouped));
    }

    public function dropdown(Action ...$actions): static
    {
        return $this->nest(new ActionCollection($actions, ActionCollectionType::Dropdown));
    }
}
