<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\ValueObjects;

use BrickNPC\EloquentTables\Aggregates\Sum;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\AggregateScope;
use BrickNPC\EloquentTables\ValueObjects\FooterRow;
use BrickNPC\EloquentTables\Concerns\AggregatesValues;

/**
 * @internal
 */
#[CoversClass(FooterRow::class)]
#[UsesClass(Sum::class)]
#[UsesClass(AggregatesValues::class)]
#[UsesClass(AggregateScope::class)]
class FooterRowTest extends TestCase
{
    public function test_it_carries_its_aggregate_scope_and_label(): void
    {
        $aggregate = new Sum();

        $row = new FooterRow($aggregate, AggregateScope::Total, 'All invoices');

        $this->assertSame($aggregate, $row->aggregate);
        $this->assertSame(AggregateScope::Total, $row->scope);
        $this->assertSame('All invoices', $row->label);
    }

    public function test_it_has_no_label_column_unless_one_is_given(): void
    {
        $this->assertNull(new FooterRow(new Sum(), AggregateScope::Page, 'This page')->labelColumn);
    }

    public function test_it_keeps_a_declared_label_column(): void
    {
        $row = new FooterRow(new Sum(), AggregateScope::Page, 'This page', labelColumn: 'name');

        $this->assertSame('name', $row->labelColumn);
    }

    public function test_a_string_label_resolves_to_itself(): void
    {
        $this->assertSame(
            'This page',
            new FooterRow(new Sum(), AggregateScope::Page, 'This page')->resolveLabel(),
        );
    }

    public function test_a_closure_label_is_resolved(): void
    {
        $this->assertSame(
            'Deferred',
            new FooterRow(new Sum(), AggregateScope::Page, static fn () => 'Deferred')->resolveLabel(),
        );
    }

    public function test_a_string_label_matching_a_php_function_is_not_called(): void
    {
        // 'Log' and 'Key' are callable strings, so resolving on is_callable would call them.
        foreach (['Log', 'Key', 'Sort'] as $label) {
            $this->assertSame(
                $label,
                new FooterRow(new Sum(), AggregateScope::Page, $label)->resolveLabel(),
            );
        }
    }

    public function test_a_row_may_go_without_a_label(): void
    {
        $row = new FooterRow(new Sum(), AggregateScope::Page);

        $this->assertNull($row->label);
        $this->assertNull($row->resolveLabel());
    }
}
