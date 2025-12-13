<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

enum ActionIntentType: string
{
    case Http  = 'http';
    case Modal = 'modal';
    case Text  = 'text';
}
