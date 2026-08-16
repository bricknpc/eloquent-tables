<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Enums;

use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\AccentStyle;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(AccentStyle::class)]
class AccentStyleTest extends TestCase
{
    #[DataProvider('accentProvider')]
    public function test_it_returns_a_bare_colour_token(AccentStyle $style, string $expected): void
    {
        $this->assertSame($expected, $style->toCssClass(Theme::Bootstrap5));
    }

    public static function accentProvider(): \Generator
    {
        yield [AccentStyle::Primary, 'primary'];

        yield [AccentStyle::Success, 'success'];

        yield [AccentStyle::Danger, 'danger'];

        yield [AccentStyle::Dark, 'dark'];
    }

    public function test_it_returns_a_disabled_token(): void
    {
        $this->assertSame('dark', AccentStyle::Primary->toCssDisabledClass(Theme::Bootstrap5));
    }

    public function test_it_returns_an_active_token(): void
    {
        $this->assertSame('light', AccentStyle::Primary->toCssActiveClass(Theme::Bootstrap5));
    }
}
