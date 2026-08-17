<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Aggregates;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Contracts\Aggregate;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Concerns\IgnoresNullValues;

final readonly class Average implements Aggregate
{
    use IgnoresNullValues;

    /**
     * @param Collection<int, mixed> $values
     */
    public function forPage(Collection $values): mixed
    {
        return $this->toFloat($this->present($values)->avg());
    }

    public function forQuery(Builder $query, string $column): mixed
    {
        return $this->toFloat($query->avg($column));
    }

    public function carriesColumnUnit(): bool
    {
        return true;
    }

    /**
     * An average is a real number, and the two scopes disagree on type without this: PHP returns an
     * int for an exact division while the database returns a float.
     */
    private function toFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
