<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\ValueObjects;

use BrickNPC\EloquentTables\Enums\RowStyle;
use BrickNPC\EloquentTables\Contracts\Aggregate;
use BrickNPC\EloquentTables\Enums\AggregateScope;

final readonly class FooterRow
{
    /**
     * @param (\Closure(): string)|string $label
     * @param RowStyle[]                  $styles
     */
    public function __construct(
        public Aggregate $aggregate,
        public AggregateScope $scope,
        public \Closure|string $label,
        public ?string $labelColumn = null,
        public array $styles = [],
    ) {}

    public function resolveLabel(): string
    {
        // Tested against is_callable, which would call a label that happens to name a PHP function.
        return $this->label instanceof \Closure
            ? (string) call_user_func($this->label)
            : $this->label;
    }
}
