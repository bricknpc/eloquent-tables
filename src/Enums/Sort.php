<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

enum Sort: string
{
    case Asc  = 'asc';
    case Desc = 'desc';
}
