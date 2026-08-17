<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Aggregates;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Contracts\Aggregate;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Concerns\IgnoresNullValues;

final readonly class Max implements Aggregate
{
    use IgnoresNullValues;

    /**
     * @param Collection<int, mixed> $values
     */
    public function forPage(Collection $values): mixed
    {
        return $this->present($values)->max();
    }

    public function forQuery(Builder $query, string $column): mixed
    {
        return $query->max($column);
    }

    public function carriesColumnUnit(): bool
    {
        return true;
    }
}
