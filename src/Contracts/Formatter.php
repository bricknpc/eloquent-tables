<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Formatter
{
    /**
     * The model is absent when there is no row to format against, as in a table footer, where a
     * value is aggregated across many rows rather than read from one.
     *
     * @template TModel of Model
     *
     * @param null|TModel $model
     */
    public function format(mixed $value, ?Model $model = null): string|\Stringable;
}
