<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contracts;

use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

interface AttributeActionCapability extends ActionCapability
{
    public function apply(ActionDescriptor $descriptor, ActionContext $context): void;
}
