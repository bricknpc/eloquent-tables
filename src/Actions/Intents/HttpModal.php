<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Intents;

use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\Enums\ActionIntentType;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;

final readonly class HttpModal extends ActionIntent
{
    public function __construct(LazyValue|string $title, LazyValue|string $url)
    {
        parent::__construct(ActionIntentType::Modal, [
            'title' => $title,
            'url'   => $url,
        ]);
    }

    public function view(): string
    {
        return 'eloquent-tables::actions.http-modal';
    }
}
