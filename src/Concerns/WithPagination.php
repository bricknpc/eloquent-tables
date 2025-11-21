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
 * @property string     $pageName       The name of the page query string parameter.
 * @property array<int> $perPageOptions The available options for the number of items to show per page. If you don't want to show this option, set it to an empty array.
 * @property string     $perPageName    The name of the per-page query string parameter.
 *
 * @phpstan-ignore trait.unused
 */
trait WithPagination
{
    public function pageName(): string
    {
        return property_exists($this, 'pageName') ? $this->pageName : 'page';
    }

    public function perPage(Request $request): int
    {
        $defaultPerPage = property_exists($this, 'perPage') ? $this->perPage : 15;

        $perPage = $request->integer($this->perPageName(), $defaultPerPage);

        return $perPage > 0 ? $perPage : $defaultPerPage;
    }

    public function perPageName(): string
    {
        return property_exists($this, 'perPageName') ? $this->perPageName : 'per_page';
    }

    public function perPageOptions(): array
    {
        return property_exists($this, 'perPageOptions') ? $this->perPageOptions : [10, 15, 25, 50, 100];
    }
}
