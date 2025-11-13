<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Concerns;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;

/**
 * @mixin Table
 */
trait WithPagination
{
    /**
     * The name of the page query string parameter.
     */
    public string $pageName = 'page';

    /**
     * The available options for the number of items to show per page. If you don't want to show this option, set it to
     * an empty array.
     *
     * @var int[]
     */
    public array $perPageOptions = [10, 15, 25, 50, 100];

    /**
     * The name of the per-page query string parameter.
     */
    public string $perPageName = 'per_page';

    /**
     * The default number of items to show per page.
     */
    protected int $perPage = 15;

    public function getPerPage(Request $request): int
    {
        $perPage = $request->integer($this->perPageName, $this->perPage);

        return $perPage > 0 ? $perPage : $this->perPage;
    }
}
