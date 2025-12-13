<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Capabilities;

use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

final class Confirmation extends ActionCapability
{
    public function __construct(
        private readonly \Closure|string $text,
        private readonly \Closure|string|null $confirmValue = null,
        private readonly \Closure|string|null $cancelValue = null,
        private readonly \Closure|string|null $inputConfirmationValue = null,
    ) {}

    public function apply(ActionDescriptor $descriptor, ActionContext $context): void
    {
        // Todo this does not work. Needs to be moved to a renderer or something
        $descriptor->attributes['tooltip']                = new LazyValue($this->text)->resolve($context);
        $descriptor->attributes['confirmValue']           = new LazyValue($this->confirmValue)->resolve($context);
        $descriptor->attributes['cancelValue']            = new LazyValue($this->cancelValue)->resolve($context);
        $descriptor->attributes['inputConfirmationValue'] = new LazyValue($this->inputConfirmationValue)->resolve($context);
    }
}
