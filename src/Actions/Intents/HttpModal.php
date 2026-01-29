<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Intents;

use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;

final class HttpModal extends ActionIntent
{
    public function __construct(
        public readonly \Closure|string $title,
        public readonly \Closure|string $url,
    ) {}

    public function view(): string
    {
        return 'eloquent-tables::actions.http-modal';
    }

    public function url(): LazyValue
    {
        return new LazyValue($this->url);
    }
}
