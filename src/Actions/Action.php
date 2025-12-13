<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Enums\ActionContextType;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\Contracts\ActionCapability;
use BrickNPC\EloquentTables\Actions\Contracts\GuardActionCapability;
use BrickNPC\EloquentTables\Actions\Contracts\AttributeActionCapability;

abstract class Action
{
    protected ActionDescriptor $descriptor;
    protected array $capabilities = [];

    public function __construct()
    {
        $this->descriptor = new ActionDescriptor();
    }

    public function label(\Closure|string $label): static
    {
        $this->descriptor->label = new LazyValue($label);

        return $this;
    }

    public function as(ActionIntent $intent): static
    {
        $this->descriptor->intent = $intent;

        return $this;
    }

    public function with(ActionCapability $capability): static
    {
        $this->capabilities[] = $capability;

        return $this;
    }

    public function descriptor(ActionContext $context): ActionDescriptor
    {
        foreach ($this->capabilities as $capability) {
            if ($capability instanceof GuardActionCapability && !$capability->check($this->descriptor, $context)) {
                continue;
            }

            if ($capability instanceof AttributeActionCapability) {
                $capability->apply($this->descriptor, $context);
            }
        }

        return $this->descriptor;
    }

    abstract public function context(): ActionContextType;
}
