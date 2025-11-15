<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Services;

use BrickNPC\EloquentTables\Enums\Theme;
use Illuminate\Contracts\Config\Repository;

readonly class Config
{
    public function __construct(
        private Repository $config,
    ) {}

    public function theme(): Theme
    {
        $theme = $this->config->get('eloquent-tables.theme', Theme::Bootstrap5);

        if (!$theme instanceof Theme) {
            $theme = Theme::Bootstrap5;
        }

        return $theme;
    }
}
