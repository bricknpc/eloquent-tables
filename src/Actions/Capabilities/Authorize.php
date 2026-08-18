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
    // @mago-expect analysis:redundant-cast -- the closure is only typed in a docblock, which PHP does not enforce, so the cast guards a user callable returning the wrong type
    {
        return (bool) call_user_func($this->authorize, $context);
    }
}
