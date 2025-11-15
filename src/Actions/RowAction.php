<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Enums\ButtonStyle;

class RowAction extends Action
{
    /**
     * @param \Closure(Model $model): string|string           $action
     * @param ButtonStyle[]                                   $styles
     * @param ?\Closure(Request $request, Model $model): bool $authorize
     * @param ?\Closure(Model $model): bool                   $when
     * @param null|\Closure(Model $model): string|string      $confirm
     */
    public function __construct(
        public \Closure|string $action,
        public string|\Stringable|null $label = null,
        public array $styles = [],
        public bool $asForm = false,
        public ?Method $method = null,
        public ?\Closure $authorize = null,
        public ?\Closure $when = null,
        public \Closure|string|null $confirm = null,
        public ?string $confirmValue = null,
    ) {
        parent::__construct(
            label: $label,
            styles: $styles,
        );
    }

    public function asForm(Method $method = Method::Post): self
    {
        $this->asForm = true;
        $this->method = $method;

        return $this;
    }

    public function method(Method $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function get(): self
    {
        return $this->asForm(Method::Get);
    }

    public function post(): self
    {
        return $this->asForm(Method::Post);
    }

    public function delete(): self
    {
        return $this->asForm(Method::Delete);
    }

    public function put(): self
    {
        return $this->asForm(Method::Put);
    }

    public function patch(): self
    {
        return $this->asForm(Method::Patch);
    }

    /**
     * @param \Closure(Request $request, Model $model): bool $authorize
     */
    public function authorize(\Closure $authorize): self
    {
        $this->authorize = $authorize;

        return $this;
    }

    /**
     * @param \Closure(Model $model): bool $when
     */
    public function when(\Closure $when): self
    {
        $this->when = $when;

        return $this;
    }

    /**
     * @param null|\Closure(Model $model): string|string $confirm
     */
    public function confirm(\Closure|string|null $confirm = null, ?string $confirmValue = null): self
    {
        /** @var string $defaultConfirm */
        $defaultConfirm = __('Are you sure?');

        $this->confirm      = $confirm ?? $defaultConfirm;
        $this->confirmValue = $confirmValue;

        return $this;
    }
}
