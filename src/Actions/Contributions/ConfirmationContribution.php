<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contributions;

use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Support\Htmlable;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

final class ConfirmationContribution extends CapabilityContribution
{
    private readonly string $id;

    public function __construct(
        private readonly string $text,
        private readonly ?string $confirmationText = null,
        private readonly ?string $cancelText = null,
        private readonly ?string $understandValue = null,
    ) {
        $this->id = md5(uniqid(more_entropy: true));
    }

    public function renderAttributes(ActionDescriptor $descriptor, ActionContext $context): Htmlable|string|\Stringable|View  // @phpstan-ignore-line
    {
        return \view('eloquent-tables::actions.contribution.confirmation-attributes', [
            'theme'           => $context->config->theme(),
            'id'              => $this->id,
            'dataNamespace'   => $context->config->dataNamespace(),
            'understandValue' => $this->understandValue,
            'isBulk'          => $context->isBulk,
        ]);
    }

    public function renderAfter(ActionDescriptor $descriptor, ActionContext $context): Htmlable|string|\Stringable|View // @phpstan-ignore-line
    {
        return \view('eloquent-tables::actions.contribution.confirmation-modal', [
            'theme'            => $context->config->theme(),
            'id'               => $this->id,
            'dataNamespace'    => $context->config->dataNamespace(),
            'understandValue'  => $this->understandValue,
            'text'             => $this->text,
            'confirmationText' => $this->confirmationText,
            'cancelText'       => $this->cancelText,
            'isBulk'           => $context->isBulk,
        ]);
    }
}
