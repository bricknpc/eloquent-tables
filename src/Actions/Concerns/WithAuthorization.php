<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Concerns;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Actions\Contracts\Authorizable;
use BrickNPC\EloquentTables\Actions\Contracts\GuardsAction;

/**
 * @template TModel of Model
 *
 * @implements Authorizable<TModel>
 * @implements GuardsAction<TModel>
 */
trait WithAuthorization
{
    public ?\Closure $authorize = null;

    public function authorize(\Closure $authorize): static
    {
        $this->authorize = $authorize;

        return $this;
    }

    /**
     * @param null|TModel $model
     */
    public function can(Request $request, ?Model $model = null): bool
    {
        return $this->authorize ? call_user_func($this->authorize, $request, $model) : true;
    }
}
