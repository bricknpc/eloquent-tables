<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\ValueObjects;

use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\ValueObjects\ResolvedFooter;
use BrickNPC\EloquentTables\ValueObjects\ResolvedFooterRow;

/**
 * @internal
 */
#[CoversClass(ResolvedFooter::class)]
#[CoversClass(ResolvedFooterRow::class)]
class ResolvedFooterTest extends TestCase
{
    public function test_a_footer_with_no_rows_is_empty(): void
    {
        $this->assertTrue(new ResolvedFooter([], 0)->isEmpty());
    }

    public function test_a_footer_with_rows_is_not_empty(): void
    {
        $footer = new ResolvedFooter([new ResolvedFooterRow('Total', ['30'])], 1);

        $this->assertFalse($footer->isEmpty());
    }

    public function test_it_carries_its_rows_and_label_span(): void
    {
        $row = new ResolvedFooterRow('Total', ['', '30']);

        $footer = new ResolvedFooter([$row], 2);

        $this->assertSame([$row], $footer->rows);
        $this->assertSame(2, $footer->labelSpan);
    }

    public function test_a_row_carries_its_label_values_and_label_index(): void
    {
        $row = new ResolvedFooterRow('Total', ['', '30'], labelIndex: 0);

        $this->assertSame('Total', $row->label);
        $this->assertSame(['', '30'], $row->values);
        $this->assertSame(0, $row->labelIndex);
    }

    public function test_a_row_spans_by_default(): void
    {
        $this->assertNull(new ResolvedFooterRow('Total', [])->labelIndex);
    }
}
