<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

enum TableStyle: string
{
    case Default = 'default';

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

    case Borderless     = 'borderless';
    case Bordered       = 'bordered';
    case Striped        = 'striped';
    case StripedColumns = 'striped-columns';
    case Hover          = 'hover';
    case Active         = 'active';

    public function toCssClass(Theme $theme): string
    {
        return match ($theme) {
            Theme::Bootstrap5 => match ($this) {
                self::Default => '',
                default       => sprintf('table-%s', $this->value),
            },
        };
    }

    public function affectsHeader(): bool
    {
        return match ($this) {
            self::Primary,
            self::Secondary,
            self::Tertiary,
            self::Quaternary,
            self::Success,
            self::Warning,
            self::Danger,
            self::Info,
            self::Light,
            self::Dark => true,
            default    => false,
        };
    }

    public function getHeaderClassStyle(Theme $theme): string
    {
        return match ($theme) {
            Theme::Bootstrap5 => match ($this) {
                self::Default => '',
                default       => $this->value,
            },
        };
    }
}
