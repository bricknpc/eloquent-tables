<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Exceptions\ActionIntentAlreadySet;
use BrickNPC\EloquentTables\Actions\Contracts\ActionCapability;

class Action
{
    protected ActionDescriptor $descriptor;

    /**
     * @var ActionCapability[]
     */
    protected array $capabilities = [];

    public function __construct()
    {
        $this->descriptor = new ActionDescriptor();
    }

    /**
     * @template TModel of Model
     *
     * @param \Closure(ActionContext<TModel> $context): string|string $label
     *
     * @return $this
     */
    public function label(\Closure|string $label): static
    {
        $this->descriptor->label = new LazyValue($label);

        return $this;
    }

    /**
     * @return $this
     *
     * @throws ActionIntentAlreadySet
     */
    public function as(ActionIntent $intent): static
    {
        if ($this->descriptor->intent !== null) {
            throw ActionIntentAlreadySet::forIntent($this->descriptor->intent, $intent, $this);
        }

        $this->descriptor->intent = $intent;

        return $this;
    }

    public function with(ActionCapability $capability): static
    {
        $this->capabilities[] = $capability;

        return $this;
    }

    /**
     * @template TModel of Model
     *
     * @param ActionContext<TModel> $context
     */
    public function descriptor(ActionContext $context): ?ActionDescriptor
    {
        if (array_any(
            $this->capabilities,
            fn (ActionCapability $capability) => !$capability->check($this->descriptor, $context),
        )) {
            return null;
        }

        foreach ($this->capabilities as $capability) {
            $capability->apply($this->descriptor, $context);

            $contribution = $capability->contribute($this->descriptor, $context);

            if ($contribution === null) {
                continue;
            }

            $this->descriptor->beforeRender->add($contribution->renderBefore($this->descriptor, $context));
            $this->descriptor->attributesRender->add($contribution->renderAttributes($this->descriptor, $context));
            $this->descriptor->afterRender->add($contribution->renderAfter($this->descriptor, $context));
        }

        return $this->descriptor;
    }
}
