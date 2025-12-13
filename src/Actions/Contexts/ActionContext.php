<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contexts;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\ActionContextType;

/**
 * @template TModel of Model
 */
final readonly class ActionContext
{
    /**
     * @param null|TModel $model
     */
    public function __construct(
        public ActionContextType $context,
        public Request $request,
        public ?Model $model = null,
    ) {}

    /**
     * @return $this
     */
    public static function table(Request $request): self
    {
        return new self(ActionContextType::Table, $request);
    }

    /**
     * @return $this
     */
    public static function mass(Request $request): self
    {
        return new self(ActionContextType::Mass, $request);
    }

    /**
     * @param TModel $model
     *
     * @return $this
     */
    public static function row(Request $request, Model $model): self
    {
        return new self(ActionContextType::Row, $request, $model);
    }
}
