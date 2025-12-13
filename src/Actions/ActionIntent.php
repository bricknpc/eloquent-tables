<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use BrickNPC\EloquentTables\Enums\ActionIntentType;

abstract readonly class ActionIntent
{
    public function __construct(
        public ActionIntentType $type,
        public array $payload = [],
    ) {}

    abstract public function view(): string;
}
