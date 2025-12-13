<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Intents;

use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\Enums\ActionIntentType;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;

final readonly class Modal extends ActionIntent
{
    public function __construct(LazyValue|string $title, LazyValue|string $content)
    {
        parent::__construct(ActionIntentType::Modal, [
            'title'   => $title,
            'content' => $content,
        ]);
    }

    public function view(): string
    {
        return 'eloquent-tables::actions.modal';
    }
}
