<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Capabilities;

use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\Contributions\TooltipContribution;

final class Tooltip extends ActionCapability
{
    /**
     * @template TModel of Model
     *
     * @param \Closure(ActionContext<TModel> $context): string|string $text
     */
    public function __construct(
        private readonly \Closure|string $text,
    ) {}

    /**
     * @template TModel of Model
     *
     * @param ActionContext<TModel> $context
     */
    public function contribute(ActionDescriptor $descriptor, ActionContext $context): CapabilityContribution
    {
        return new TooltipContribution(new LazyValue($this->text)->resolve($context));
    }
}
