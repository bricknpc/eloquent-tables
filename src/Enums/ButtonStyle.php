<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

enum ButtonStyle: string
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

    public function toCssClass(Theme $theme): string
    {
        return match ($theme) {
            Theme::Bootstrap5 => match ($this) {
                self::Default => '',
                default       => sprintf('btn-%s', $this->value),
            },
        };
    }
}
