<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Resources;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Contracts\Aggregate;
use Illuminate\Contracts\Database\Query\Builder;

final readonly class ArrayAggregate implements Aggregate
{
    /**
     * @param Collection<int, mixed> $values
     */
    public function forPage(Collection $values): mixed
    {
        return $values->all();
    }

    public function forQuery(Builder $query, string $column): mixed
    {
        return [];
    }

    public function carriesColumnUnit(): bool
    {
        return false;
    }
}
