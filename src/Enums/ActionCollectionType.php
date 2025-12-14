<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

enum ActionCollectionType
{
    case Normal;
    case Grouped;
    case Dropdown;
}
