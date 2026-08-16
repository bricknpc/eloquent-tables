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

    public function pageQueryName(): string
    {
        /** @var string $pageQueryName */
        $pageQueryName = $this->config->get('eloquent-tables.pagination.page_query_name', 'page');

        return $pageQueryName;
    }

    public function perPageQueryName(): string
    {
        /** @var string $perPageQueryName */
        $perPageQueryName = $this->config->get('eloquent-tables.pagination.per_page_query_name', 'per_page');

        return $perPageQueryName;
    }

    public function preferencesEnabled(): bool
    {
        return (bool) $this->config->get('eloquent-tables.preferences.enabled', true);
    }

    public function preferencesCookieName(): string
    {
        /** @var string $cookieName */
        $cookieName = $this->config->get('eloquent-tables.preferences.cookie_name', 'eloquent_tables_preferences');

        return $cookieName;
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

    public function checkIcon(): string|\Stringable
    {
        return $this->icon('check', "\u{2713}");
    }

    public function crossIcon(): string|\Stringable
    {
        return $this->icon('cross', "\u{2717}");
    }

    private function icon(string $name, ?string $default = null): string|\Stringable
    {
        /** @var string|\Stringable $icon */
        $icon = $this->config->get('eloquent-tables.icons.' . $name, $default);

        return $icon;
    }
}
