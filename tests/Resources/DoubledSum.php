<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Resources;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Contracts\Aggregate;
use Illuminate\Contracts\Database\Query\Builder;

final readonly class DoubledSum implements Aggregate
{
    public function __construct(
        private int $factor = 2,
    ) {}

    /**
     * @param Collection<int, mixed> $values
     */
    public function forPage(Collection $values): float|int|string|\Stringable|null
    {
        return $values->sum() * $this->factor;
    }

    public function forQuery(Builder $query, string $column): float|int|string|\Stringable|null
    {
        return $query->sum($column) * $this->factor;
    }

    public function carriesColumnUnit(): bool
    {
        return true;
    }
}
