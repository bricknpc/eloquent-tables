<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contexts;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Services\Config;

final readonly class ActionContext
{
    public function __construct(
        public Request $request,
        public Config $config,
        public ?Model $model = null,
        public bool $asDropdown = false,
    ) {}

    public function asDropdown(): self
    {
        return new self($this->request, $this->config, $this->model, true);
    }
}
