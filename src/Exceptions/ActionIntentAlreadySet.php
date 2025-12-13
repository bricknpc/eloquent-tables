<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Exceptions;

use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Actions\ActionIntent;

class ActionIntentAlreadySet extends \Exception
{
    private ActionIntent $intent;
    private Action $action;

    public static function forIntent(ActionIntent $intent, Action $action): self
    {
        $exception = new self(
            sprintf('The action %s already has an intent % set', get_class($action), get_class($intent)),
        );

        $exception->intent = $intent;
        $exception->action = $action;

        return $exception;
    }

    /**
     * @return array<string, Action|ActionIntent>
     */
    public function context(): array
    {
        return [
            'intent' => $this->intent,
            'action' => $this->action,
        ];
    }
}
