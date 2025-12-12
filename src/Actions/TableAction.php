<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Htmlable;
use BrickNPC\EloquentTables\Enums\ButtonStyle;

class TableAction extends Action
{
    /**
     * @param ButtonStyle[]                         $styles
     * @param null|\Closure(Request $request): bool $authorize
     */
    public function __construct(
        public string $action,
        public Htmlable|string|\Stringable|null $label = null,
        public array $styles = [],
        public ?string $tooltip = null,
        public bool $asModal = false,
        public ?\Closure $authorize = null,
    ) {
        parent::__construct(
            label: $label,
            styles: $styles,
        );
    }

    public function tooltip(string $tooltip): self
    {
        $this->tooltip = $tooltip;

        return $this;
    }

    public function asModal(): self
    {
        $this->asModal = true;

        return $this;
    }

    /**
     * @param \Closure(Request $request): bool $authorize
     */
    public function authorize(\Closure $authorize): self
    {
        $this->authorize = $authorize;

        return $this;
    }
}
