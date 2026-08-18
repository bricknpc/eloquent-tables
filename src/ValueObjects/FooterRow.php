<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\ValueObjects;

use BrickNPC\EloquentTables\Enums\RowStyle;
use BrickNPC\EloquentTables\Contracts\Aggregate;
use BrickNPC\EloquentTables\Enums\AggregateScope;

final readonly class FooterRow
{
    /**
     * @param null|(\Closure(): string)|string $label  a sum or a count often speaks for itself, so a
     *                                                 row may go without one
     * @param RowStyle[]                       $styles
     */
    public function __construct(
        public Aggregate $aggregate,
        public AggregateScope $scope,
        public \Closure|string|null $label = null,
        public ?string $labelColumn = null,
        public array $styles = [],
    ) {}

    public function resolveLabel(): ?string
    {
        // @mago-expect analysis:redundant-cast -- the closure is only typed in a docblock, which PHP does not enforce, so the cast guards a user callable returning the wrong type
        // Tested against is_callable, which would call a label that happens to name a PHP function.
        return $this->label instanceof \Closure ? (string) call_user_func($this->label) : $this->label;
    }
}
