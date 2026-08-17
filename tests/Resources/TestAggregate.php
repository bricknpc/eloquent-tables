<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Resources;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Contracts\Aggregate;
use Illuminate\Contracts\Database\Query\Builder;

final readonly class TestAggregate implements Aggregate
{
    public function __construct(
        private float|int|string|\Stringable|null $pageValue = null,
        private float|int|string|\Stringable|null $queryValue = null,
        private bool $carriesColumnUnit = true,
    ) {}

    /**
     * @param Collection<int, mixed> $values
     */
    public function forPage(Collection $values): float|int|string|\Stringable|null
    {
        return $this->pageValue;
    }

    public function forQuery(Builder $query, string $column): float|int|string|\Stringable|null
    {
        return $this->queryValue;
    }

    public function carriesColumnUnit(): bool
    {
        return $this->carriesColumnUnit;
    }
}
