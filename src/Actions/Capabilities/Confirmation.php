<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Capabilities;

use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\Contributions\ConfirmationContribution;

final class Confirmation extends ActionCapability
{
    /**
     * @param \Closure(ActionContext $context): string|string      $text
     * @param null|\Closure(ActionContext $context): string|string $confirmValue
     * @param null|\Closure(ActionContext $context): string|string $cancelValue
     * @param null|\Closure(ActionContext $context): string|string $inputConfirmationValue
     */
    public function __construct(
        private readonly \Closure|string $text,
        private readonly \Closure|string|null $confirmValue = null,
        private readonly \Closure|string|null $cancelValue = null,
        private readonly \Closure|string|null $inputConfirmationValue = null,
    ) {}

    public function contribute(ActionDescriptor $descriptor, ActionContext $context): CapabilityContribution
    {
        return new ConfirmationContribution(
            new LazyValue($this->text)->resolve($context) ?? '',
            new LazyValue($this->confirmValue)->resolve($context),
            new LazyValue($this->cancelValue)->resolve($context),
            new LazyValue($this->inputConfirmationValue)->resolve($context),
        );
    }
}
