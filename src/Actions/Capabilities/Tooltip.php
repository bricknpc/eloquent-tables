<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Capabilities;

use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\Contracts\AttributeActionCapability;

final readonly class Tooltip implements AttributeActionCapability
{
    public function __construct(
        private LazyValue|string $text,
    ) {}

    public function apply(ActionDescriptor $descriptor, ActionContext $context): void
    {
        $descriptor->attributes['tooltip'] = $this->text instanceof LazyValue ? $this->text->resolve($context) : $this->text;
    }
}
