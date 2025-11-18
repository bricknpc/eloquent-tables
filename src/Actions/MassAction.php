<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Enums\Method;
use Illuminate\Contracts\Support\Htmlable;

class MassAction extends Action
{
    /**
     * @param null|\Closure(Request $request): bool $authorize
     */
    public function __construct(
        public string $action,
        Htmlable|string|\Stringable|null $label = null,
        array $styles = [],
        public Method $method = Method::Post,
        public ?\Closure $authorize = null,
        public ?string $confirm = null,
        public ?string $confirmValue = null,
        public ?string $tooltip = null,
    ) {
        parent::__construct($label, $styles);
    }

    public function method(Method $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function get(): self
    {
        return $this->method(Method::Get);
    }

    public function post(): self
    {
        return $this->method(Method::Post);
    }

    public function put(): self
    {
        return $this->method(Method::Put);
    }

    public function patch(): self
    {
        return $this->method(Method::Patch);
    }

    public function delete(): self
    {
        return $this->method(Method::Delete);
    }

    /**
     * @param \Closure(Request $request): bool $authorize
     */
    public function authorize(\Closure $authorize): self
    {
        $this->authorize = $authorize;

        return $this;
    }

    public function confirm(?string $confirm = null, ?string $confirmValue = null): self
    {
        /** @var string $defaultMessage */
        $defaultMessage = __('Are you sure?');

        $this->confirm      = $confirm ?? $defaultMessage;
        $this->confirmValue = $confirmValue;

        return $this;
    }

    public function tooltip(string $tooltip): self
    {
        $this->tooltip = $tooltip;

        return $this;
    }
}
