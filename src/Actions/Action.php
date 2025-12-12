<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Htmlable;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\Concerns\Authorizable;
use BrickNPC\EloquentTables\Actions\Contracts\WithAuthorization;

/**
 * @template TModel of Model
 *
 * @implements Authorizable<TModel>
 */
abstract class Action implements Authorizable
{
    /**
     * @use WithAuthorization<TModel>
     */
    use WithAuthorization;

    /**
     * @param ButtonStyle[] $styles
     */
    public function __construct(
        public Htmlable|string|\Stringable|null $label = null,
        public array $styles = [],
    ) {}

    public function label(Htmlable|string|\Stringable $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function styles(ButtonStyle ...$styles): static
    {
        $this->styles = array_merge($this->styles, $styles);

        return $this;
    }
}
