<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Concerns;

use Illuminate\Support\Collection;

trait IgnoresNullValues
{
    /**
     * Drops null values so a page-scoped aggregate agrees with its query-scoped counterpart,
     * because every SQL aggregate ignores NULL.
     *
     * @param Collection<int, mixed> $values
     *
     * @return Collection<int, mixed>
     */
    protected function present(Collection $values): Collection
    {
        return $values->filter(fn (mixed $value) => $value !== null)->values();
    }
}
