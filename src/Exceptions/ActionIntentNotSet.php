<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Exceptions;

use BrickNPC\EloquentTables\Actions\Action;

class ActionIntentNotSet extends \Exception
{
    private ?Action $action = null;

    public static function forAction(Action $action): self
    {
        $exception = new self(sprintf(
            'The action %s has no intent and can not be rendered, set one with the as() method',
            get_class($action),
        ));

        $exception->action = $action;

        return $exception;
    }

    /**
     * @return array<string, null|Action>
     */
    public function context(): array
    {
        return [
            'action' => $this->action,
        ];
    }
}
