<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Contracts;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Database\Query\Builder;

interface Filter
{
    public string $name {get; }

    /**
     * @param Collection<int, Htmlable|Model|\Stringable>|iterable<mixed, mixed> $options
     */
    public function __construct(string $name, Collection|iterable $options);

    public function __invoke(Request $request, Builder $query, string $value): void;

    public function view(): string;

    /**
     * @return array<string, string>
     */
    public function options(): array;
}
