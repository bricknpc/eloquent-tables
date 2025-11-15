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

    public function searchQueryName(): string
    {
        /** @var string $searchQueryName */
        $searchQueryName = $this->config->get('eloquent-tables.search.query_name', 'search');

        return $searchQueryName;
    }

    public function sortQueryName(): string
    {
        /** @var string $searchQueryName */
        $searchQueryName = $this->config->get('eloquent-tables.sorting.query_name', 'sort');

        return $searchQueryName;
    }
}
