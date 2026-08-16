<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Services;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
readonly class TablePreferences
{
    public function __construct(
        private Config $config,
    ) {}

    /**
     * @param Table<TModel> $table
     */
    public function perPage(Table $table, Request $request): ?int
    {
        $perPage = $this->stored($table, $request)['per_page'] ?? null;

        if (is_int($perPage)) {
            return $perPage > 0 ? $perPage : null;
        }

        return is_string($perPage) && ctype_digit($perPage) && (int) $perPage > 0 ? (int) $perPage : null;
    }

    /**
     * @param Table<TModel> $table
     *
     * @return array<string, string>
     */
    public function sort(Table $table, Request $request): array
    {
        $sort = $this->stored($table, $request)['sort'] ?? null;

        if (!is_array($sort)) {
            return [];
        }

        $values = [];

        // The stored order is the precedence the visitor built up, so it is preserved as-is.
        foreach ($sort as $column => $direction) {
            if (is_string($direction) && $direction !== '') {
                $values[(string) $column] = $direction;
            }
        }

        return $values;
    }

    /**
     * @param Table<TModel> $table
     *
     * @return array<mixed>
     */
    private function stored(Table $table, Request $request): array
    {
        if (!$this->config->preferencesEnabled()) {
            return [];
        }

        $cookie = $request->cookie($this->config->preferencesCookieName());

        if (!is_string($cookie) || $cookie === '') {
            return [];
        }

        $decoded = json_decode($cookie, true);

        if (!is_array($decoded)) {
            return [];
        }

        $stored = $decoded[$table->name()] ?? null;

        return is_array($stored) ? $stored : [];
    }
}
