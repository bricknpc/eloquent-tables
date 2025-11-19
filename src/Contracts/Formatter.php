<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Formatter
{
    /**
     * @template TModel of Model
     *
     * @param TModel $model
     */
    public function format(mixed $value, Model $model): \Stringable;
}
