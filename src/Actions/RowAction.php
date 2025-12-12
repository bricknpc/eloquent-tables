<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\Method;
use Illuminate\Contracts\Support\Htmlable;
use BrickNPC\EloquentTables\Enums\ButtonStyle;

/**
 * @template TModel of Model
 *
 * @extends Action<TModel>
 */
class RowAction extends Action
{
    /**
     * @param \Closure(TModel $model): string|string      $action
     * @param null|\Closure(TModel $model): string|string $tooltip
     * @param ButtonStyle[]                               $styles
     * @param ?\Closure(TModel $model): bool              $when
     * @param null|\Closure(TModel $model): string|string $confirm
     */
    public function __construct(
        public \Closure|string $action,
        public Htmlable|string|\Stringable|null $label = null,
        public \Closure|string|null $tooltip = null,
        public array $styles = [],
        public bool $asForm = false,
        public Method $method = Method::Post,
        public ?\Closure $when = null,
        public \Closure|string|null $confirm = null,
        public ?string $confirmValue = null,
    ) {
        parent::__construct(
            label: $label,
            styles: $styles,
        );
    }

    /**
     * @param \Closure(TModel $model): string|string $tooltip
     *
     * @return $this
     */
    public function tooltip(\Closure|string $tooltip): static
    {
        $this->tooltip = $tooltip;

        return $this;
    }

    /**
     * @return $this
     */
    public function asForm(Method $method = Method::Post): static
    {
        $this->asForm = true;
        $this->method = $method;

        return $this;
    }

    /**
     * @return $this
     */
    public function method(Method $method): static
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return $this
     */
    public function get(): static
    {
        return $this->asForm(Method::Get);
    }

    /**
     * @return $this
     */
    public function post(): static
    {
        return $this->asForm(Method::Post);
    }

    /**
     * @return $this
     */
    public function delete(): static
    {
        return $this->asForm(Method::Delete);
    }

    /**
     * @return $this
     */
    public function put(): static
    {
        return $this->asForm(Method::Put);
    }

    /**
     * @return $this
     */
    public function patch(): static
    {
        return $this->asForm(Method::Patch);
    }

    /**
     * @param \Closure(TModel $model): bool $when
     *
     * @return $this
     */
    public function when(\Closure $when): static
    {
        $this->when = $when;

        return $this;
    }

    /**
     * @param null|\Closure(TModel $model): string|string $confirm
     *
     * @return $this
     */
    public function confirm(\Closure|string|null $confirm = null, ?string $confirmValue = null): static
    {
        /** @var string $defaultConfirm */
        $defaultConfirm = __('Are you sure?');

        $this->confirm      = $confirm ?? $defaultConfirm;
        $this->confirmValue = $confirmValue;

        return $this;
    }
}
