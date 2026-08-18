<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

use BrickNPC\EloquentTables\Contracts\Style;

enum AccentStyle: string implements Style
{
    case Primary    = 'primary';
    case Secondary  = 'secondary';
    case Tertiary   = 'tertiary';
    case Quaternary = 'quaternary';
    case Success    = 'success';
    case Warning    = 'warning';
    case Danger     = 'danger';
    case Info       = 'info';
    case Light      = 'light';
    case Dark       = 'dark';

    public function toCssClass(Theme $theme): string
    {
        return match ($theme) { Theme::Bootstrap5 => $this->value };
    }

    public function toCssDisabledClass(Theme $theme): string
    {
        return match ($theme) { Theme::Bootstrap5 => 'dark' };
    }

    public function toCssActiveClass(Theme $theme): string
    {
        return match ($theme) {
            Theme::Bootstrap5 => match ($this) {
                self::Primary, self::Secondary, self::Success, self::Dark, self::Danger => 'light',
                default => 'dark',
            },
        };
    }
}
