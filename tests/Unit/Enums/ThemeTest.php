<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Enums;

use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Theme::class)]
class ThemeTest extends TestCase
{
    #[DataProvider('themeProvider')]
    public function test_it_returns_correct_links_view(Theme $theme, string $expectedView): void
    {
        $this->assertSame($expectedView, $theme->getLinksView());
    }

    public static function themeProvider(): \Generator
    {
        yield [
            Theme::Bootstrap5,
            'eloquent-tables::bootstrap-5.pagination',
        ];
    }
}
