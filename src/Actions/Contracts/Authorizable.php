<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contracts;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 *
 * @extends GuardsAction<TModel>
 *
 * @phpstan-type AuthorizeCallback \Closure(Request $request, ?TModel $model): bool
 */
interface Authorizable extends GuardsAction
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
