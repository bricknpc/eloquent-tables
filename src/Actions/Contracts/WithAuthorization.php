<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contracts;

use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Actions\Concerns\Authorizable;

/**
 * @template TModel of Model
 *
 * @implements Authorizable<TModel>
 */
trait WithAuthorization
{
    public ?\Closure $authorize = null;

    public function authorize(\Closure $authorize): static
    {
        $this->authorize = $authorize;

        return $this;
    }
}
