<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Enums;

use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\AggregateScope;

/**
 * @internal
 */
#[CoversClass(AggregateScope::class)]
class AggregateScopeTest extends TestCase
{
    public function test_it_has_a_scope_for_the_page_and_for_the_whole_result_set(): void
    {
        $this->assertSame([AggregateScope::Page, AggregateScope::Total], AggregateScope::cases());
    }

    public function test_it_has_no_other_scopes(): void
    {
        $this->assertCount(2, AggregateScope::cases());
    }
}
