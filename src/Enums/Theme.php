<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

enum Theme: string
{
    case Bootstrap5 = 'bootstrap-5';

    public function getLinksView(): string
    {
        return match ($this) {
            self::Bootstrap5 => 'pagination::bootstrap-5',
        };
    }
}
