<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Capabilities;

use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

final class Authorize extends ActionCapability
{
    /**
     * @param \Closure(ActionContext $context): bool $authorize
     */
    public function __construct(
        private readonly \Closure $authorize,
    ) {}

    public function check(ActionDescriptor $descriptor, ActionContext $context): bool
    {
        return call_user_func($this->authorize, $context);
    }
}
