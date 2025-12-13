<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Exceptions;

use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Actions\ActionIntent;

class ActionIntentAlreadySet extends \Exception
{
    private ActionIntent $intent;
    private ActionIntent $newIntent;
    private Action $action;

    public static function forIntent(ActionIntent $intent, ActionIntent $newIntent, Action $action): self
    {
        $exception = new self(
            sprintf(
                'The action %s already has an intent %, new intent %s can not be set',
                get_class($action),
                get_class($intent),
                get_class($newIntent),
            ),
        );

        $exception->intent    = $intent;
        $exception->newIntent = $newIntent;
        $exception->action    = $action;

        return $exception;
    }

    /**
     * @return array<string, Action|ActionIntent>
     */
    public function context(): array
    {
        return [
            'intent'    => $this->intent,
            'newIntent' => $this->newIntent,
            'action'    => $this->action,
        ];
    }
}
