<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Resources;

use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Contracts\Style;

enum TestStyle implements Style
{
    case First;
    case Second;
    case Third;

    public function toCssClass(Theme $theme): string
    {
        return match ($theme) {
            Theme::Bootstrap5 => match ($this) {
                self::First  => 'first',
                self::Second => 'second',
                self::Third  => 'third',
            },
        };
    }
}
