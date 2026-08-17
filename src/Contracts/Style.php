<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Contracts;

use BrickNPC\EloquentTables\Enums\Theme;

interface Style
{
    public function toCssClass(Theme $theme): string;
}
