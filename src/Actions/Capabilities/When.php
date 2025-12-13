<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Capabilities;

use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\Contracts\GuardActionCapability;

final readonly class When implements GuardActionCapability
{
    /**
     * @param \Closure(ActionContext $context): bool $condition
     */
    public function __construct(
        public \Closure $condition,
    ) {}

    public function check(ActionDescriptor $descriptor, ActionContext $context): bool
    {
        return call_user_func($this->condition, $context);
    }
}
