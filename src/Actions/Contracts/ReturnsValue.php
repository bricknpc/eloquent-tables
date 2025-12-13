<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contracts;

interface ReturnsValue
{
    public function getValue(): string;
}
