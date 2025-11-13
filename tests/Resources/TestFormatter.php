<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Resources;

use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Contracts\Formatter;

final readonly class TestFormatter implements Formatter
{
    public function format(mixed $value, Model $model): \Stringable
    {
        return str('formatted');
    }
}
