<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Resources;

class TestTableAuthorisationFailsCustomCallback extends TestTableAuthorisationFails
{
    public function unauthorized(): void
    {
        throw new \RuntimeException('Custom unauthorized exception.');
    }
}
