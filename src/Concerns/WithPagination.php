<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Concerns;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 *
 * @mixin Table<TModel>
 *
 * @property int        $perPage        The number of items to show per page.
 * @property array<int> $perPageOptions The available options for the number of items to show per page. If you don't want to show this option, set it to an empty array.
 */
trait WithPagination
{
    public function perPage(Request $request): int
    {
        $perPage = property_exists($this, 'perPage') ? $this->perPage : 15;

        return $perPage > 0 ? $perPage : 15;
    }

    /**
     * @return array<int>
     */
    public function perPageOptions(): array
    {
        return property_exists($this, 'perPageOptions') ? $this->perPageOptions : [10, 15, 25, 50, 100];
    }
}
