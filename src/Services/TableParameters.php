<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Services;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\TableParameter;

/**
 * @template TModel of Model
 */
readonly class TableParameters
{
    /**
     * @param TablePreferences<TModel> $preferences
     */
    public function __construct(
        private Config $config,
        private TablePreferences $preferences,
    ) {}

    /**
     * @param Table<TModel> $table
     */
    public function key(Table $table, TableParameter $parameter): string
    {
        return sprintf('%s[%s]', $table->name(), $this->subKey($parameter));
    }

    /**
     * @param Table<TModel> $table
     *
     * @return array<string, string>
     */
    public function arrayValue(Table $table, TableParameter $parameter, Request $request): array
    {
        $value = $this->raw($table, $parameter, $request);

        if (!is_array($value)) {
            // A stored sort applies only when the request says nothing about sorting at all. A key
            // that is present but empty means the visitor cycled every column off, which is a
            // choice in its own right and must not be undone by the stored value.
            $absent = $value === null;

            return $absent && $parameter === TableParameter::Sort
                ? $this->preferences->sort($table, $request)
                : [];
        }

        $values = [];

        // The insertion order is meaningful: it is the order the visitor clicked the column headers.
        foreach ($value as $key => $item) {
            if (is_string($item) && $item !== '') {
                $values[(string) $key] = $item;
            }
        }

        return $values;
    }

    /**
     * @param Table<TModel> $table
     */
    public function stringValue(Table $table, TableParameter $parameter, Request $request): ?string
    {
        $value = $this->raw($table, $parameter, $request);

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @param Table<TModel> $table
     */
    public function integerValue(Table $table, TableParameter $parameter, Request $request): ?int
    {
        $value = $this->raw($table, $parameter, $request);

        if (is_int($value)) {
            return $value;
        }

        return is_string($value) && ctype_digit($value) ? (int) $value : null;
    }

    /**
     * @param Table<TModel> $table
     */
    public function perPage(Table $table, Request $request, int $default): int
    {
        $perPage = $this->integerValue($table, TableParameter::PerPage, $request)
            ?? $this->preferences->perPage($table, $request);

        return $perPage !== null && $perPage > 0 ? $perPage : $default;
    }

    /**
     * The current query string as flat name/value pairs in bracket notation, minus the given names.
     *
     * A GET form replaces the whole query string with its own fields, so every control re-emits this
     * as hidden inputs. Without it, changing one control discards the rest of the page's state,
     * including the other tables'.
     *
     * @param string[] $except fully-qualified names to omit, e.g. "users[filter][active]"; everything
     *                         nested below a given name is omitted with it
     *
     * @return array<string, string>
     */
    public function hiddenInputs(Request $request, array $except): array
    {
        $inputs = [];

        $query = $request->query();

        foreach ($this->flatten(is_array($query) ? $query : []) as $name => $value) {
            if (!$this->isExcluded($name, $except)) {
                $inputs[$name] = $value;
            }
        }

        return $inputs;
    }

    /**
     * @param string[] $except
     */
    private function isExcluded(string $name, array $except): bool
    {
        foreach ($except as $excluded) {
            if ($name === $excluded || str_starts_with($name, $excluded . '[')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<array-key, mixed> $values
     *
     * @return array<string, string>
     */
    private function flatten(array $values, string $prefix = ''): array
    {
        $flat = [];

        foreach ($values as $key => $value) {
            $name = $prefix === '' ? (string) $key : $prefix . '[' . $key . ']';

            if (is_array($value)) {
                foreach ($this->flatten($value, $name) as $nestedName => $nestedValue) {
                    $flat[$nestedName] = $nestedValue;
                }

                continue;
            }

            if (is_scalar($value)) {
                $flat[$name] = (string) $value;
            }
        }

        return $flat;
    }

    /**
     * @param Table<TModel> $table
     */
    private function raw(Table $table, TableParameter $parameter, Request $request): mixed
    {
        $namespace = $request->query($table->name());

        if (!is_array($namespace)) {
            return null;
        }

        return $namespace[$this->subKey($parameter)] ?? null;
    }

    private function subKey(TableParameter $parameter): string
    {
        return match ($parameter) {
            TableParameter::Search  => $this->config->searchQueryName(),
            TableParameter::Sort    => $this->config->sortQueryName(),
            TableParameter::Filter  => $this->config->filterQueryName(),
            TableParameter::PerPage => $this->config->perPageQueryName(),
            TableParameter::Page    => $this->config->pageQueryName(),
        };
    }
}
