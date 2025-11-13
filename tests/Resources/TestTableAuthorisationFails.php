<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Resources;

use Illuminate\Http\Request;

class TestTableAuthorisationFails extends TestTable
{
    protected function authorize(Request $request): bool
    {
        return false;
    }
}
