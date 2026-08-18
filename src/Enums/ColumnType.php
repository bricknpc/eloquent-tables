<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

enum ColumnType
{
    case Text;
    case Checkbox;
    case Boolean;

    /**
     * @return CellStyle[]
     */
    public function defaultStyles(): array
    {
        return match ($this) {
            self::Text => [],
            self::Checkbox, self::Boolean => [CellStyle::AlignCenter],
        };
    }

    public function getTdView(): string
    {
        return match ($this) {
            self::Text => 'td-text',
            self::Checkbox => 'td-checkbox',
            self::Boolean => 'td-boolean',
        };
    }

    public function getThView(): string
    {
        return match ($this) {
            self::Text => 'th-text',
            self::Checkbox => 'th-checkbox',
            self::Boolean => 'th-boolean',
        };
    }
}
