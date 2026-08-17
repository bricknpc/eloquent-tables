<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Contracts;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Database\Query\Builder;

interface Aggregate
{
    /**
     * Aggregates the values of one column across the rows on the current page.
     *
     * Returns null when this aggregate has no answer, which leaves the footer cell empty. The
     * result is a value for presentation in a cell, so it is narrower than the values it reads.
     *
     * @param Collection<int, mixed> $values
     */
    public function forPage(Collection $values): float|int|string|\Stringable|null;

    /**
     * Aggregates one column across the whole result set, by pushing the work into the query.
     *
     * Returns null when this aggregate has no portable query form, which leaves the footer cell empty.
     */
    public function forQuery(Builder $query, string $column): float|int|string|\Stringable|null;

    /**
     * Whether the result is in the same unit as the column, and so should be rendered through
     * the column's formatter. A sum of a money column is money; a count of it is not.
     */
    public function carriesColumnUnit(): bool;
}
