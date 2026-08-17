<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Aggregates;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Contracts\Aggregate;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Concerns\AggregatesValues;

final readonly class Min implements Aggregate
{
    use AggregatesValues;

    /**
     * @param Collection<int, mixed> $values
     */
    public function forPage(Collection $values): float|int|string|\Stringable|null
    {
        return $this->presentable($this->present($values)->min());
    }

    public function forQuery(Builder $query, string $column): float|int|string|\Stringable|null
    {
        return $this->presentable($query->min($column));
    }

    public function carriesColumnUnit(): bool
    {
        return true;
    }
}
