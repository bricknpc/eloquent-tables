<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\ValueObjects;

final readonly class ResolvedFooter
{
    /**
     * @param ResolvedFooterRow[] $rows
     * @param int                 $labelSpan cells a spanning label covers, shared by every row so
     *                                       stacked figures stay comparable
     */
    public function __construct(
        public array $rows,
        public int $labelSpan,
    ) {}

    public function isEmpty(): bool
    {
        return $this->rows === [];
    }
}
