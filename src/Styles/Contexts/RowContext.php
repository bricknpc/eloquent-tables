<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Styles\Contexts;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Contracts\StyleContext;

final readonly class RowContext implements StyleContext
{
    public function __construct(
        public Request $request,
        public Model $model,
    ) {}
}
