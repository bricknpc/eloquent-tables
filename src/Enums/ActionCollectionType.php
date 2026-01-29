<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

enum ActionCollectionType
{
    case Normal;
    case Grouped;
    case Dropdown;

    public function view(): string
    {
        return match ($this) {
            self::Normal   => 'eloquent-tables::actions.collection.default',
            self::Grouped  => 'eloquent-tables::actions.collection.group',
            self::Dropdown => 'eloquent-tables::actions.collection.dropdown',
        };
    }
}
