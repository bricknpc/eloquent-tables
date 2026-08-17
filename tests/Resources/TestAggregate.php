<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Resources;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Contracts\Aggregate;
use Illuminate\Contracts\Database\Query\Builder;

final readonly class TestAggregate implements Aggregate
{
    public function __construct(
        private mixed $pageValue = null,
        private mixed $queryValue = null,
        private bool $carriesColumnUnit = true,
    ) {}

    /**
     * @param Collection<int, mixed> $values
     */
    public function forPage(Collection $values): mixed
    {
        return $this->pageValue;
    }

    public function forQuery(Builder $query, string $column): mixed
    {
        return $this->queryValue;
    }

    public function carriesColumnUnit(): bool
    {
        return $this->carriesColumnUnit;
    }
}
