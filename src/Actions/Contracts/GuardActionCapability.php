<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contracts;

use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

interface GuardActionCapability extends ActionCapability
{
    public function check(ActionDescriptor $descriptor, ActionContext $context): bool;
}
