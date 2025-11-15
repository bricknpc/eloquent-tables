<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use BrickNPC\EloquentTables\Enums\ButtonStyle;

class TableAction extends Action
{
    /**
     * @param ButtonStyle[] $styles
     */
    public function __construct(
        public string $action,
        public string|\Stringable|null $label = null,
        public array $styles = [],
        public bool $asModal = false,
    ) {
        parent::__construct(
            label: $label,
            styles: $styles,
        );
    }

    public function asModal(): self
    {
        $this->asModal = true;

        return $this;
    }
}
