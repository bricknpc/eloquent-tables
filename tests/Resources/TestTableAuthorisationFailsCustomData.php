<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Resources;

use Symfony\Component\HttpFoundation\Response;

class TestTableAuthorisationFailsCustomData extends TestTableAuthorisationFails
{
    public function unauthorizedMessage(): string
    {
        return 'This is a custom message.';
    }

    protected function unauthorizedResponseCode(): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
