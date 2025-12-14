<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contexts;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

final readonly class ActionContext
{
    public function __construct(
        public Request $request,
        public ?Model $model = null,
    ) {}
}
