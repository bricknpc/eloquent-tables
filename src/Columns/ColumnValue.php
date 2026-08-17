<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Columns;

use BrickNPC\EloquentTables\Column;
use Illuminate\Database\Eloquent\Model;

readonly class ColumnValue
{
    /**
     * @template TModel of Model
     *
     * @param Column<TModel> $column
     * @param TModel         $model
     */
    public function resolve(Column $column, Model $model): mixed
    {
        return $column->valueUsing instanceof \Closure
            ? call_user_func($column->valueUsing, $model)
            : $model->{$column->name};
    }
}
