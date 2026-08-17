<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\ValueObjects;

final readonly class ResolvedFooter
{
    /**
     * @param ResolvedFooterRow[] $rows
     * @param int                 $labelSpan       cells a spanning label covers, shared by every row
     *                                             so stacked figures stay comparable
     * @param int                 $firstValueIndex the column index a spanning row's values start at
     */
    public function __construct(
        public array $rows,
        public int $labelSpan,
        public int $firstValueIndex = 0,
    ) {}

    public function isEmpty(): bool
    {
        return $this->rows === [];
    }
}
