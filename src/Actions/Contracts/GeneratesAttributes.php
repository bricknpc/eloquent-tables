<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contracts;

interface GeneratesAttributes
{
    /**
     * @return array<string, string>
     */
    public function getAttributes(): array;
}
