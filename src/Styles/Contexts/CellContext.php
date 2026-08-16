<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Styles\Contexts;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\TableRegion;
use BrickNPC\EloquentTables\Contracts\StyleContext;

/**
 * @template TModel of Model
 */
final readonly class CellContext implements StyleContext
{
    /**
     * @param Column<TModel> $column
     * @param null|TModel    $model
     */
    public function __construct(
        public Request $request,
        public Column $column,
        public ?Model $model = null,
        public TableRegion $region = TableRegion::Body,
    ) {}
}
