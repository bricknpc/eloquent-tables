<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use Illuminate\Contracts\View\View;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

final readonly class ActionRenderer
{
    public function __construct(
        private Config $config,
    ) {}

    public function render(Action $action, ActionContext $context): View
    {
        $descriptor = $action->descriptor($context);

        return view($descriptor->intent->view(), [
            'theme'         => $this->config->theme(),
            'dataNamespace' => $this->config->dataNamespace(),
            'type'          => $descriptor->element,
            'attributes'    => $descriptor->attributes,
            'intent'        => $descriptor->intent,
            'payload'       => $descriptor->intent->payload,
        ]);
    }
}
