<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Concerns;

use Illuminate\Support\Collection;

trait AggregatesValues
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
        return $values->filter(static fn(mixed $value) => $value !== null)->values();
    }

    /**
     * Narrows a computed result to something a table cell can render. Anything else has no
     * presentable form, so it becomes an empty cell rather than a rendering error.
     */
    protected function presentable(mixed $value): float|int|string|\Stringable|null
    {
        return is_int($value) || is_float($value) || is_string($value) || $value instanceof \Stringable ? $value : null;
    }
}
