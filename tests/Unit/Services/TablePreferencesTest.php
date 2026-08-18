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
use BrickNPC\EloquentTables\Services\TablePreferences;

/**
 * @internal
 */
#[CoversClass(TablePreferences::class)]
#[UsesClass(Config::class)]
#[UsesClass(Table::class)]
class TablePreferencesTest extends TestCase
{
    public function test_it_reads_a_stored_per_page(): void
    {
        $request = $this->requestWithPreferences(['users' => ['per_page' => 50]]);

        $this->assertSame(50, $this->preferences()->perPage($this->table('users'), $request));
    }

    public function test_it_reads_a_stored_sort_in_the_stored_order(): void
    {
        // Covers AE1. The order carries the precedence, so it must survive the round trip.
        $request = $this->requestWithPreferences([
            'users' => ['sort' => ['name' => 'desc', 'email' => 'asc']],
        ]);

        $this->assertSame(
            ['name' => 'desc', 'email' => 'asc'],
            $this->preferences()->sort($this->table('users'), $request),
        );
    }

    public function test_one_tables_preferences_do_not_apply_to_another(): void
    {
        $request = $this->requestWithPreferences(['users' => ['per_page' => 50]]);

        $preferences = $this->preferences();

        $this->assertSame(50, $preferences->perPage($this->table('users'), $request));
        $this->assertNull($preferences->perPage($this->table('orders'), $request));
    }

    public function test_it_reads_nothing_when_preferences_are_disabled(): void
    {
        // Covers AE8.
        config()->set('eloquent-tables.preferences.enabled', false);

        $request = $this->requestWithPreferences([
            'users' => ['per_page' => 50, 'sort' => ['name' => 'desc']],
        ]);

        $preferences = $this->preferences();
        $table       = $this->table('users');

        $this->assertNull($preferences->perPage($table, $request));
        $this->assertSame([], $preferences->sort($table, $request));
    }

    public function test_it_reads_from_the_configured_cookie_name(): void
    {
        config()->set('eloquent-tables.preferences.cookie_name', 'prefs');

        $request = Request::create('/');
        $request->cookies->set('prefs', json_encode(['users' => ['per_page' => 50]]));

        $this->assertSame(50, $this->preferences()->perPage($this->table('users'), $request));
    }

    #[DataProvider('unusableCookies')]
    public function test_an_unusable_cookie_yields_no_preferences(?string $cookie): void
    {
        // A corrupted or hand-edited cookie must not break rendering.
        $request = Request::create('/');

        if ($cookie !== null) {
            $request->cookies->set('eloquent_tables_preferences', $cookie);
        }

        $preferences = $this->preferences();
        $table       = $this->table('users');

        $this->assertNull($preferences->perPage($table, $request));
        $this->assertSame([], $preferences->sort($table, $request));
    }

    public static function unusableCookies(): \Generator
    {
        yield 'absent' => [null];

        yield 'empty' => [''];

        yield 'not json' => ['{not json'];

        yield 'json scalar' => ['"a string"'];

        yield 'json list' => ['[1, 2, 3]'];
    }

    #[DataProvider('unusablePerPageValues')]
    public function test_an_unusable_stored_per_page_is_ignored(mixed $stored): void
    {
        $request = $this->requestWithPreferences(['users' => ['per_page' => $stored]]);

        $this->assertNull($this->preferences()->perPage($this->table('users'), $request));
    }

    public static function unusablePerPageValues(): \Generator
    {
        yield 'zero' => [0];

        yield 'negative' => [-5];

        yield 'not numeric' => ['lots'];

        yield 'array' => [[50]];
    }

    public function test_an_unusable_stored_sort_is_ignored(): void
    {
        $request = $this->requestWithPreferences([
            'users' => ['sort' => ['name' => 'desc', 'email' => ['nested'], 'team' => '']],
        ]);

        $this->assertSame(['name' => 'desc'], $this->preferences()->sort($this->table('users'), $request));
    }

    public function test_a_stored_entry_that_is_not_an_array_is_ignored(): void
    {
        $request = $this->requestWithPreferences(['users' => 'nonsense']);

        $preferences = $this->preferences();
        $table       = $this->table('users');

        $this->assertNull($preferences->perPage($table, $request));
        $this->assertSame([], $preferences->sort($table, $request));
    }

    public function test_a_stored_sort_that_is_not_an_array_is_ignored(): void
    {
        $request = $this->requestWithPreferences(['users' => ['sort' => 'name']]);

        $this->assertSame([], $this->preferences()->sort($this->table('users'), $request));
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

    private function preferences(): TablePreferences
    {
        /** @var TablePreferences $preferences */
        $preferences = $this->app->make(TablePreferences::class);

        return $preferences;
    }

    private function table(string $name): Table
    {
        return new class($name) extends Table {
            public function __construct(
                private readonly string $tableName,
            ) {}

            public function name(): string
            {
                return $this->tableName;
            }
        };
    }
}
