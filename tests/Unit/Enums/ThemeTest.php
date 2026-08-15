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

    #[DataProvider('viewProvider')]
    public function test_it_namespaces_a_view_for_the_theme(Theme $theme, string $view, string $expectedView): void
    {
        $this->assertSame($expectedView, $theme->view($view));
    }

    public static function viewProvider(): \Generator
    {
        yield 'top level view' => [
            Theme::Bootstrap5,
            'table',
            'eloquent-tables::bootstrap-5.table',
        ];

        yield 'nested view keeps its dots' => [
            Theme::Bootstrap5,
            'actions.collection.dropdown',
            'eloquent-tables::bootstrap-5.actions.collection.dropdown',
        ];

        yield 'empty view name' => [
            Theme::Bootstrap5,
            '',
            'eloquent-tables::bootstrap-5.',
        ];
    }

    #[DataProvider('themeCaseProvider')]
    public function test_it_prefixes_every_theme_with_its_own_value(Theme $theme): void
    {
        $this->assertSame('eloquent-tables::' . $theme->value . '.table', $theme->view('table'));
    }

    #[DataProvider('themeCaseProvider')]
    public function test_the_links_view_follows_the_view_convention(Theme $theme): void
    {
        $this->assertSame($theme->view('pagination'), $theme->getLinksView());
    }

    public static function themeCaseProvider(): \Generator
    {
        foreach (Theme::cases() as $theme) {
            yield $theme->value => [$theme];
        }
    }
}
