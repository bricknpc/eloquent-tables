<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contracts;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface GuardsAction
{
    /**
     * @param null|TModel $model
     */
    public function can(Request $request, ?Model $model = null): bool;
}
