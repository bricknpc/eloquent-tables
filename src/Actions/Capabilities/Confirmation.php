<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Capabilities;

use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\Contracts\AttributeActionCapability;

final readonly class Confirmation implements AttributeActionCapability
{
    public function __construct(
        public LazyValue|string $text,
        public LazyValue|string|null $confirmValue = null,
        public LazyValue|string|null $cancelValue = null,
        public LazyValue|string|null $inputConfirmationValue = null,
    ) {}

    public function apply(ActionDescriptor $descriptor, ActionContext $context): void
    {
        $descriptor->attributes['tooltip']                = $this->text instanceof LazyValue ? $this->text->resolve($context) : $this->text;
        $descriptor->attributes['confirmValue']           = $this->confirmValue instanceof LazyValue ? $this->confirmValue->resolve($context) : $this->confirmValue;
        $descriptor->attributes['cancelValue']            = $this->cancelValue instanceof LazyValue ? $this->cancelValue->resolve($context) : $this->cancelValue;
        $descriptor->attributes['inputConfirmationValue'] = $this->inputConfirmationValue instanceof LazyValue ? $this->inputConfirmationValue->resolve($context) : $this->inputConfirmationValue;
    }
}
