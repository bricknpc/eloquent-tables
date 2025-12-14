<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contracts;

use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

interface ActionCapability
{
    /**
     * @template TModel of Model
     *
     * @param ActionContext<TModel> $context
     */
    public function check(ActionDescriptor $descriptor, ActionContext $context): bool;

    /**
     * @template TModel of Model
     *
     * @param ActionContext<TModel> $context
     */
    public function apply(ActionDescriptor $descriptor, ActionContext $context): void;

    /**
     * @template TModel of Model
     *
     * @param ActionContext<TModel> $context
     */
    public function contribute(ActionDescriptor $descriptor, ActionContext $context): ?CapabilityContribution;
}
