<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

enum ActionContextType: string
{
    case Table = 'table';
    case Row   = 'row';
    case Mass  = 'mass';
}
