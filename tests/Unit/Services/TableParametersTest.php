<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Services;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use BrickNPC\EloquentTables\Enums\TableParameter;
use BrickNPC\EloquentTables\Services\TableParameters;
use BrickNPC\EloquentTables\Services\TablePreferences;

/**
 * @internal
 */
#[CoversClass(TableParameters::class)]
#[UsesClass(Config::class)]
#[UsesClass(Table::class)]
#[UsesClass(TablePreferences::class)]
class TableParametersTest extends TestCase
{
    #[DataProvider('parameterKeys')]
    public function test_it_builds_a_nested_key_for_every_parameter(TableParameter $parameter, string $expected): void
    {
        $this->assertSame($expected, $this->parameters()->key($this->table('users'), $parameter));
    }

    public static function parameterKeys(): \Generator
    {
        yield 'search' => [TableParameter::Search, 'users[search]'];

        yield 'sort' => [TableParameter::Sort, 'users[sort]'];

        yield 'filter' => [TableParameter::Filter, 'users[filter]'];

        yield 'per page' => [TableParameter::PerPage, 'users[per_page]'];

        yield 'page' => [TableParameter::Page, 'users[page]'];
    }

    public function test_the_key_uses_the_configured_query_names(): void
    {
        config()->set('eloquent-tables.sorting.query_name', 'order');
        config()->set('eloquent-tables.pagination.per_page_query_name', 'size');

        $parameters = $this->parameters();
        $table      = $this->table('users');

        $this->assertSame('users[order]', $parameters->key($table, TableParameter::Sort));
        $this->assertSame('users[size]', $parameters->key($table, TableParameter::PerPage));
    }

    public function test_it_reads_an_array_parameter(): void
    {
        $request = Request::create('/?users[sort][name]=asc&users[sort][email]=desc');

        $this->assertSame(
            ['name' => 'asc', 'email' => 'desc'],
            $this->parameters()->arrayValue($this->table('users'), TableParameter::Sort, $request),
        );
    }

    public function test_it_preserves_the_order_of_an_array_parameter(): void
    {
        $request = Request::create('/?users[sort][email]=desc&users[sort][name]=asc');

        $this->assertSame(
            ['email', 'name'],
            array_keys($this->parameters()->arrayValue($this->table('users'), TableParameter::Sort, $request)),
        );
    }

    public function test_it_reads_only_its_own_table_parameters(): void
    {
        $request = Request::create('/?users[sort][name]=asc&orders[sort][total]=desc');

        $parameters = $this->parameters();

        $this->assertSame(
            ['name' => 'asc'],
            $parameters->arrayValue($this->table('users'), TableParameter::Sort, $request),
        );
        $this->assertSame(
            ['total' => 'desc'],
            $parameters->arrayValue($this->table('orders'), TableParameter::Sort, $request),
        );
    }

    public function test_it_ignores_a_flat_parameter_that_is_not_namespaced(): void
    {
        $request = Request::create('/?sort[name]=asc&search=ada');

        $parameters = $this->parameters();
        $table      = $this->table('users');

        $this->assertSame([], $parameters->arrayValue($table, TableParameter::Sort, $request));
        $this->assertNull($parameters->stringValue($table, TableParameter::Search, $request));
    }

    public function test_it_returns_an_empty_array_when_the_namespace_is_not_an_array(): void
    {
        $request = Request::create('/?users=ada');

        $this->assertSame(
            [],
            $this->parameters()->arrayValue($this->table('users'), TableParameter::Sort, $request),
        );
    }

    public function test_it_returns_an_empty_array_when_the_parameter_is_not_an_array(): void
    {
        $request = Request::create('/?users[sort]=name');

        $this->assertSame(
            [],
            $this->parameters()->arrayValue($this->table('users'), TableParameter::Sort, $request),
        );
    }

    public function test_it_drops_array_entries_that_are_not_usable_values(): void
    {
        $request = Request::create('/?users[filter][active]=1&users[filter][empty]=&users[filter][nested][deep]=x');

        $this->assertSame(
            ['active' => '1'],
            $this->parameters()->arrayValue($this->table('users'), TableParameter::Filter, $request),
        );
    }

    public function test_it_reads_a_string_parameter(): void
    {
        $request = Request::create('/?users[search]=ada');

        $this->assertSame(
            'ada',
            $this->parameters()->stringValue($this->table('users'), TableParameter::Search, $request),
        );
    }

    #[DataProvider('unusableStringValues')]
    public function test_it_returns_null_for_an_unusable_string_parameter(string $query): void
    {
        $request = Request::create('/?' . $query);

        $this->assertNull(
            $this->parameters()->stringValue($this->table('users'), TableParameter::Search, $request),
        );
    }

    public static function unusableStringValues(): \Generator
    {
        yield 'absent' => ['users[sort][name]=asc'];

        yield 'empty' => ['users[search]='];

        yield 'array' => ['users[search][deep]=ada'];
    }

    public function test_it_reads_an_integer_parameter(): void
    {
        $request = Request::create('/?users[per_page]=50');

        $this->assertSame(
            50,
            $this->parameters()->integerValue($this->table('users'), TableParameter::PerPage, $request),
        );
    }

    public function test_it_reads_an_integer_parameter_that_is_already_an_integer(): void
    {
        // A request built in code rather than parsed from a URL carries real integers.
        $request = Request::create('/');
        $request->query->set('users', ['per_page' => 50]);

        $this->assertSame(
            50,
            $this->parameters()->integerValue($this->table('users'), TableParameter::PerPage, $request),
        );
    }

    #[DataProvider('unusableIntegerValues')]
    public function test_it_returns_null_for_an_unusable_integer_parameter(string $query): void
    {
        $request = Request::create('/?' . $query);

        $this->assertNull(
            $this->parameters()->integerValue($this->table('users'), TableParameter::PerPage, $request),
        );
    }

    public static function unusableIntegerValues(): \Generator
    {
        yield 'absent' => ['users[search]=ada'];

        yield 'not numeric' => ['users[per_page]=lots'];

        yield 'negative' => ['users[per_page]=-5'];

        yield 'array' => ['users[per_page][deep]=50'];
    }

    public function test_per_page_prefers_the_visitors_choice(): void
    {
        $request = Request::create('/?users[per_page]=50');

        $this->assertSame(
            50,
            $this->parameters()->perPage($this->table('users'), $request, 15),
        );
    }

    #[DataProvider('perPageFallbacks')]
    public function test_per_page_falls_back_to_the_tables_default(string $query): void
    {
        $request = Request::create('/?' . $query);

        $this->assertSame(
            15,
            $this->parameters()->perPage($this->table('users'), $request, 15),
        );
    }

    public static function perPageFallbacks(): \Generator
    {
        yield 'absent' => ['users[search]=ada'];

        yield 'zero' => ['users[per_page]=0'];

        yield 'not numeric' => ['users[per_page]=lots'];

        yield 'another table' => ['orders[per_page]=50'];
    }

    public function test_hidden_inputs_flatten_the_query_to_bracket_notation(): void
    {
        $request = Request::create('/?users[sort][name]=asc&users[search]=ada&orders[page]=2');

        $this->assertSame(
            [
                'users[sort][name]' => 'asc',
                'users[search]'     => 'ada',
                'orders[page]'      => '2',
            ],
            $this->parameters()->hiddenInputs($request, []),
        );
    }

    public function test_hidden_inputs_omit_an_excluded_name(): void
    {
        $request = Request::create('/?users[search]=ada&users[per_page]=50');

        $this->assertSame(
            ['users[per_page]' => '50'],
            $this->parameters()->hiddenInputs($request, ['users[search]']),
        );
    }

    public function test_hidden_inputs_omit_everything_below_an_excluded_name(): void
    {
        $request = Request::create('/?users[sort][name]=asc&users[sort][email]=desc&users[search]=ada');

        $this->assertSame(
            ['users[search]' => 'ada'],
            $this->parameters()->hiddenInputs($request, ['users[sort]']),
        );
    }

    public function test_hidden_inputs_keep_sibling_filters(): void
    {
        $request = Request::create('/?users[filter][active]=1&users[filter][team]=7&users[search]=ada');

        $this->assertSame(
            [
                'users[filter][team]' => '7',
                'users[search]'       => 'ada',
            ],
            $this->parameters()->hiddenInputs($request, ['users[filter][active]']),
        );
    }

    public function test_hidden_inputs_keep_another_tables_parameters(): void
    {
        $request = Request::create('/?users[search]=ada&orders[sort][total]=desc');

        $this->assertSame(
            ['orders[sort][total]' => 'desc'],
            $this->parameters()->hiddenInputs($request, ['users']),
        );
    }

    public function test_hidden_inputs_are_empty_for_a_bare_request(): void
    {
        $this->assertSame([], $this->parameters()->hiddenInputs(Request::create('/'), []));
    }

    public function test_a_stored_sort_applies_when_the_request_carries_none(): void
    {
        // Covers AE1.
        $request = $this->requestWithPreferences([
            'users' => ['sort' => ['name' => 'desc', 'email' => 'asc']],
        ]);

        $this->assertSame(
            ['name' => 'desc', 'email' => 'asc'],
            $this->parameters()->arrayValue($this->table('users'), TableParameter::Sort, $request),
        );
    }

    public function test_a_sort_in_the_request_wins_over_the_stored_one(): void
    {
        // Covers AE2.
        $request = $this->requestWithPreferences([
            'users' => ['sort' => ['name' => 'desc']],
        ]);
        $request->query->set('users', ['sort' => ['email' => 'asc']]);

        $this->assertSame(
            ['email' => 'asc'],
            $this->parameters()->arrayValue($this->table('users'), TableParameter::Sort, $request),
        );
    }

    public function test_a_per_page_in_the_request_wins_over_the_stored_one(): void
    {
        // Covers AE2.
        $request = $this->requestWithPreferences(['users' => ['per_page' => 50]]);
        $request->query->set('users', ['per_page' => '10']);

        $this->assertSame(10, $this->parameters()->perPage($this->table('users'), $request, 15));
    }

    public function test_a_stored_per_page_applies_when_the_request_carries_none(): void
    {
        $request = $this->requestWithPreferences(['users' => ['per_page' => 50]]);

        $this->assertSame(50, $this->parameters()->perPage($this->table('users'), $request, 15));
    }

    public function test_the_declared_default_applies_when_nothing_is_stored_or_requested(): void
    {
        $this->assertSame(
            15,
            $this->parameters()->perPage($this->table('users'), Request::create('/'), 15),
        );
    }

    public function test_a_stored_preference_is_ignored_when_preferences_are_disabled(): void
    {
        // Covers AE8.
        config()->set('eloquent-tables.preferences.enabled', false);

        $request = $this->requestWithPreferences([
            'users' => ['per_page' => 50, 'sort' => ['name' => 'desc']],
        ]);

        $parameters = $this->parameters();
        $table      = $this->table('users');

        $this->assertSame(15, $parameters->perPage($table, $request, 15));
        $this->assertSame([], $parameters->arrayValue($table, TableParameter::Sort, $request));
    }

    public function test_only_sort_falls_back_to_a_stored_preference(): void
    {
        // Search and filters are deliberately not persisted.
        $request = $this->requestWithPreferences([
            'users' => ['filter' => ['active' => '1']],
        ]);

        $this->assertSame(
            [],
            $this->parameters()->arrayValue($this->table('users'), TableParameter::Filter, $request),
        );
    }

    public function test_an_explicitly_cleared_sort_does_not_fall_back_to_the_stored_one(): void
    {
        // Covers AE5. Cycling every column off leaves "users[sort]=" in the URL, which means the
        // visitor cleared the sort — distinct from never having chosen one.
        $request = $this->requestWithPreferences([
            'users' => ['sort' => ['name' => 'desc']],
        ]);
        $request->query->set('users', ['sort' => '']);

        $this->assertSame(
            [],
            $this->parameters()->arrayValue($this->table('users'), TableParameter::Sort, $request),
        );
    }

    /**
     * @param array<string, mixed> $preferences
     */
    private function requestWithPreferences(array $preferences): Request
    {
        $request = Request::create('/');
        $request->cookies->set('eloquent_tables_preferences', json_encode($preferences));

        return $request;
    }

    private function parameters(): TableParameters
    {
        /** @var TableParameters $parameters */
        $parameters = $this->app->make(TableParameters::class);

        return $parameters;
    }

    private function table(string $name): Table
    {
        return new class($name) extends Table {
            public function __construct(private readonly string $tableName) {}

            public function name(): string
            {
                return $this->tableName;
            }
        };
    }
}
