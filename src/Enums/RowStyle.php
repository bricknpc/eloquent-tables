<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

use BrickNPC\EloquentTables\Contracts\Style;

enum RowStyle: string implements Style
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
        return match ($theme) {
            Theme::Bootstrap5 => sprintf('table-%s', $this->value),
        };
    }
}
