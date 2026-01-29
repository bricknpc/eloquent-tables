<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contracts;

use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

interface ActionCapability
{
    public function check(ActionDescriptor $descriptor, ActionContext $context): bool;

    public function apply(ActionDescriptor $descriptor, ActionContext $context): void;

    public function contribute(ActionDescriptor $descriptor, ActionContext $context): ?CapabilityContribution;
}
