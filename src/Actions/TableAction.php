<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use BrickNPC\EloquentTables\Enums\ActionContextType;

class TableAction extends Action
{
    public function context(): ActionContextType
    {
        return ActionContextType::Table;
    }
}
