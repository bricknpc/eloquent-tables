<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

enum ActionRegion
{
    case Button;
    case DropdownItem;
    case DropdownToggle;

    public function baseCssClass(Theme $theme): string
    {
        return match ($theme) {
            Theme::Bootstrap5 => match ($this) {
                self::Button         => 'btn',
                self::DropdownItem   => 'dropdown-item',
                self::DropdownToggle => 'btn dropdown-toggle',
            },
        };
    }

    public function defaultStyle(): ?ButtonStyle
    {
        return match ($this) {
            self::Button, self::DropdownToggle => ButtonStyle::Primary,
            self::DropdownItem                 => null,
        };
    }
}
