<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Aggregates;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Contracts\Aggregate;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Concerns\AggregatesValues;

final readonly class Count implements Aggregate
{
    use AggregatesValues;

    /**
     * @param Collection<int, mixed> $values
     */
    public function forPage(Collection $values): int
    {
        return $this->present($values)->count();
    }

    public function forQuery(Builder $query, string $column): float|int|string|\Stringable|null
    {
        return $this->presentable($query->count($column));
    }

    public function carriesColumnUnit(): bool
    {
        return false;
    }
}
