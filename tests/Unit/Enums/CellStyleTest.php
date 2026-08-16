<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Enums;

use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use BrickNPC\EloquentTables\Enums\CellStyle;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\StyleFamily;
use BrickNPC\EloquentTables\Enums\StyleTarget;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(CellStyle::class)]
class CellStyleTest extends TestCase
{
    #[DataProvider('cellStyleProvider')]
    public function test_it_returns_to_correct_css_class(Theme $theme, CellStyle $style, string $expected): void
    {
        $this->assertSame($expected, $style->toCssClass($theme));
    }

    public static function cellStyleProvider(): \Generator
    {
        yield [Theme::Bootstrap5, CellStyle::AlignLeft, 'justify-content-start'];

        yield [Theme::Bootstrap5, CellStyle::AlignCenter, 'justify-content-center'];

        yield [Theme::Bootstrap5, CellStyle::AlignRight, 'justify-content-end'];

        yield [Theme::Bootstrap5, CellStyle::AlignJustify, 'justify-content-stretch'];

        yield [Theme::Bootstrap5, CellStyle::AlignBetween, 'justify-content-between'];

        yield [Theme::Bootstrap5, CellStyle::AlignTop, 'align-items-start'];

        yield [Theme::Bootstrap5, CellStyle::AlignMiddle, 'align-items-center'];

        yield [Theme::Bootstrap5, CellStyle::AlignBottom, 'align-items-end'];
    }

    #[DataProvider('appearanceProvider')]
    public function test_it_returns_the_correct_class_for_an_appearance_style(CellStyle $style, string $expected): void
    {
        $this->assertSame($expected, $style->toCssClass(Theme::Bootstrap5));
    }

    public static function appearanceProvider(): \Generator
    {
        yield 'background' => [CellStyle::BackgroundSuccess, 'table-success'];

        yield 'background dark' => [CellStyle::BackgroundDark, 'table-dark'];

        yield 'text colour' => [CellStyle::TextDanger, 'text-danger'];

        yield 'text colour light' => [CellStyle::TextLight, 'text-light'];

        yield 'bold' => [CellStyle::FontBold, 'fw-bold'];

        yield 'semibold' => [CellStyle::FontSemibold, 'fw-semibold'];

        yield 'normal' => [CellStyle::FontNormal, 'fw-normal'];

        yield 'light weight' => [CellStyle::FontLight, 'fw-light'];
    }

    #[DataProvider('targetProvider')]
    public function test_every_case_declares_its_target(CellStyle $style, StyleTarget $expected): void
    {
        $this->assertSame($expected, $style->target());
    }

    public static function targetProvider(): \Generator
    {
        yield 'alignment sits on the content' => [CellStyle::AlignRight, StyleTarget::Content];

        yield 'vertical alignment sits on the content' => [CellStyle::AlignMiddle, StyleTarget::Content];

        yield 'background fills the cell' => [CellStyle::BackgroundSuccess, StyleTarget::Cell];

        yield 'text colour sits on the cell' => [CellStyle::TextDanger, StyleTarget::Cell];

        yield 'weight sits on the cell' => [CellStyle::FontBold, StyleTarget::Cell];
    }

    #[DataProvider('familyProvider')]
    public function test_every_case_declares_its_family(CellStyle $style, StyleFamily $expected): void
    {
        $this->assertSame($expected, $style->family());
    }

    public static function familyProvider(): \Generator
    {
        yield 'horizontal alignment' => [CellStyle::AlignRight, StyleFamily::Alignment];

        yield 'vertical alignment' => [CellStyle::AlignTop, StyleFamily::Alignment];

        yield 'background' => [CellStyle::BackgroundWarning, StyleFamily::Background];

        yield 'text colour' => [CellStyle::TextInfo, StyleFamily::TextColour];

        yield 'weight' => [CellStyle::FontBold, StyleFamily::FontWeight];
    }

    public function test_every_case_has_a_target_and_a_family(): void
    {
        foreach (CellStyle::cases() as $style) {
            $this->assertInstanceOf(StyleTarget::class, $style->target());
            $this->assertInstanceOf(StyleFamily::class, $style->family());
        }
    }
}
