<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Resources;

use BrickNPC\EloquentTables\Table;
use Illuminate\Contracts\Database\Query\Builder;

class TestTable extends Table
{
    public function query(): Builder
    {
        return TestModel::query();
    }

    public function columns(): array
    {
        return [];
    }
}
