<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Concerns;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 *
 * @phpstan-type AuthorizeCallback \Closure(Request $request, ?TModel $model): bool
 */
interface Authorizable
{
    /**
     * @var null|AuthorizeCallback $authorize
     */
    public ?\Closure $authorize {get; }

    /**
     * @param AuthorizeCallback $authorize
     *
     * @return $this
     */
    public function authorize(\Closure $authorize): static;
}
