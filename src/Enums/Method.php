<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

enum Method: string
{
    case Get    = 'GET';
    case Post   = 'POST';
    case Put    = 'PUT';
    case Patch  = 'PATCH';
    case Delete = 'DELETE';
}
