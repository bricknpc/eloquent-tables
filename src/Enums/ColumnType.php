<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

enum ColumnType
{
    case Text;
    case Checkbox;
    case Boolean;

    public function getTdView(): string
    {
        return match ($this) {
            self::Text     => 'td-text',
            self::Checkbox => 'td-checkbox',
            self::Boolean  => 'td-boolean',
        };
    }
}
