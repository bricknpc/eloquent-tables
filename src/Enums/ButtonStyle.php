<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

use BrickNPC\EloquentTables\Contracts\Style;

enum ButtonStyle: string implements Style
{
    case Default           = '';
    case Primary           = 'primary';
    case PrimaryOutline    = 'outline-primary';
    case Secondary         = 'secondary';
    case SecondaryOutline  = 'outline-secondary';
    case Tertiary          = 'tertiary';
    case TertiaryOutline   = 'outline-tertiary';
    case Quaternary        = 'quaternary';
    case QuaternaryOutline = 'outline-quaternary';
    case Success           = 'success';
    case SuccessOutline    = 'outline-success';
    case Warning           = 'warning';
    case WarningOutline    = 'outline-warning';
    case Danger            = 'danger';
    case DangerOutline     = 'outline-danger';
    case Info              = 'info';
    case InfoOutline       = 'outline-info';
    case Light             = 'light';
    case LightOutline      = 'outline-light';
    case Link              = 'link';
    case Dark              = 'dark';
    case DarkOutline       = 'outline-dark';

    /**
     * A button style is rendered as text inside a dropdown, because a button inside a dropdown menu does not look
     * like the rest of the menu.
     */
    public function toCssClass(Theme $theme, ActionRegion $region = ActionRegion::Button): string
    {
        return match ($theme) {
            Theme::Bootstrap5 => match (true) {
                $this === self::Default => '',
                // An outlined button has no meaning inside a dropdown menu, so only the colour of it is used.
                $region === ActionRegion::DropdownItem => sprintf('text-%s', str_replace('outline-', '', $this->value)),
                default => sprintf('btn-%s', $this->value),
            },
        };
    }
}
