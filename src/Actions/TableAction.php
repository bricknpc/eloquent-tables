<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Htmlable;
use BrickNPC\EloquentTables\Enums\ButtonStyle;

/**
 * @template TModel of Model
 *
 * @extends Action<TModel>
 */
class TableAction extends Action
{
    /**
     * @param ButtonStyle[] $styles
     */
    public function __construct(
        public string $action,
        public Htmlable|string|\Stringable|null $label = null,
        public array $styles = [],
        public ?string $tooltip = null,
        public bool $asModal = false,
    ) {
        parent::__construct(
            label: $label,
            styles: $styles,
        );
    }

    /**
     * @return $this
     */
    public function tooltip(string $tooltip): self
    {
        $this->tooltip = $tooltip;

        return $this;
    }

    /**
     * @return $this
     */
    public function asModal(): self
    {
        $this->asModal = true;

        return $this;
    }
}
