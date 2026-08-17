<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Aggregates;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Contracts\Aggregate;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Concerns\IgnoresNullValues;

/**
 * The middle value of a column, over the current page only.
 *
 * There is no portable SQL median, so the whole result set is declined rather than loading every row.
 */
final readonly class Median implements Aggregate
{
    use IgnoresNullValues;

    /**
     * @param Collection<int, mixed> $values
     */
    public function forPage(Collection $values): mixed
    {
        return $this->present($values)->median();
    }

    public function forQuery(Builder $query, string $column): mixed
    {
        return null;
    }

    public function carriesColumnUnit(): bool
    {
        return true;
    }
}
