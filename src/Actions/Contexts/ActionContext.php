<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contexts;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
final readonly class ActionContext
{
    /**
     * @param null|TModel $model
     *
     * @todo add config
     */
    public function __construct(
        public Request $request,
        public ?Model $model = null,
    ) {}
}
