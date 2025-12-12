<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\Method;
use Illuminate\Contracts\Support\Htmlable;

/**
 * @template TModel of Model
 *
 * @extends Action<TModel>
 */
class MassAction extends Action
{
    public function __construct(
        public string $action,
        Htmlable|string|\Stringable|null $label = null,
        array $styles = [],
        public Method $method = Method::Post,
        public ?string $confirm = null,
        public ?string $confirmValue = null,
        public ?string $tooltip = null,
    ) {
        parent::__construct($label, $styles);
    }

    /**
     * @return $this
     */
    public function method(Method $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return $this
     */
    public function get(): self
    {
        return $this->method(Method::Get);
    }

    /**
     * @return $this
     */
    public function post(): self
    {
        return $this->method(Method::Post);
    }

    /**
     * @return $this
     */
    public function put(): self
    {
        return $this->method(Method::Put);
    }

    /**
     * @return $this
     */
    public function patch(): self
    {
        return $this->method(Method::Patch);
    }

    /**
     * @return $this
     */
    public function delete(): self
    {
        return $this->method(Method::Delete);
    }

    /**
     * @return $this
     */
    public function confirm(?string $confirm = null, ?string $confirmValue = null): self
    {
        /** @var string $defaultMessage */
        $defaultMessage = __('Are you sure?');

        $this->confirm      = $confirm ?? $defaultMessage;
        $this->confirmValue = $confirmValue;

        return $this;
    }

    /**
     * @return $this
     */
    public function tooltip(string $tooltip): self
    {
        $this->tooltip = $tooltip;

        return $this;
    }
}
