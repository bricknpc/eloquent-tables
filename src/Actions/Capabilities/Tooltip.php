<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Capabilities;

use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

final class Tooltip extends ActionCapability
{
    public function __construct(
        private readonly \Closure|string $text,
    ) {}

    public function apply(ActionDescriptor $descriptor, ActionContext $context): void
    {
        // todo needs to be moved
        $descriptor->attributes['tooltip'] = new LazyValue($this->text)->resolve($context);
    }
}
