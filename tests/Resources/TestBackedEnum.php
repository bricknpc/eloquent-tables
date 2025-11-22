<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Resources;

enum TestBackedEnum: string
{
    case First  = 'first';
    case Second = 'second';
    case Third  = 'third';
}
