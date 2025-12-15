<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\Contracts\ActionCapability as ActionCapabilityContract;

class ActionCapability implements ActionCapabilityContract
{
    public function check(ActionDescriptor $descriptor, ActionContext $context): bool
    {
        return true;
    }

    public function apply(ActionDescriptor $descriptor, ActionContext $context): void
    {
        // Do nothing
    }

    public function contribute(ActionDescriptor $descriptor, ActionContext $context): ?CapabilityContribution
    {
        return null;
    }
}
