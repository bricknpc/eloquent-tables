<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\ValueObjects;

final readonly class ResolvedFooterRow
{
    /**
     * @param array<int, string> $values     one entry per column, in column order, empty where the
     *                                       column offers nothing for this row's aggregate
     * @param null|int           $labelIndex the column the label sits in, or null when the label
     *                                       spans the leading columns instead
     */
    public function __construct(
        public string $label,
        public array $values,
        public ?int $labelIndex = null,
        public string $styles = '',
    ) {}
}
