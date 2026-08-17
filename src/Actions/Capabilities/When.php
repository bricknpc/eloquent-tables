<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Capabilities;

use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

final class When extends ActionCapability
{
    /**
     * @param \Closure(ActionContext $context): bool $condition
     */
    public function __construct(
        private readonly \Closure $condition,
    ) {}

    public function check(ActionDescriptor $descriptor, ActionContext $context): bool
    {
        return (bool) call_user_func($this->condition, $context);
    }
}
