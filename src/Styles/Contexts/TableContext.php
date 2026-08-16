<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Styles\Contexts;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Contracts\StyleContext;

final readonly class TableContext implements StyleContext
{
    public function __construct(
        public Request $request,
    ) {}
}
