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

    public function dataNamespace(): string
    {
        /** @var string $dataNamespace */
        $dataNamespace = $this->config->get('eloquent-tables.data-namespace', 'et');

        return $dataNamespace;
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

    public function filterQueryName(): string
    {
        /** @var string $searchQueryName */
        $searchQueryName = $this->config->get('eloquent-tables.filtering.query_name', 'filter');

        return $searchQueryName;
    }

    public function searchIcon(): string|\Stringable
    {
        return $this->icon('search', "\u{1F50D}");
    }

    public function sortNoneIcon(): string|\Stringable
    {
        return $this->icon('sort-none', "\u{25C0}");
    }

    public function sortAscIcon(): string|\Stringable
    {
        return $this->icon('sort-asc', "\u{25B2}");
    }

    public function sortDescIcon(): string|\Stringable
    {
        return $this->icon('sort-desc', "\u{25BC}");
    }

    private function icon(string $name, ?string $default = null): string|\Stringable
    {
        /** @var string|\Stringable $icon */
        $icon = $this->config->get('eloquent-tables.icons.' . $name, $default);

        return $icon;
    }
}
