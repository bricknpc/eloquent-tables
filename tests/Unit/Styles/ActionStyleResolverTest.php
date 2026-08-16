<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Styles;

use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Enums\ActionRegion;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\Tests\Resources\TestStyle;
use BrickNPC\EloquentTables\Styles\ActionStyleResolver;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

/**
 * @internal
 */
#[CoversClass(ActionStyleResolver::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(ActionRegion::class)]
#[UsesClass(ButtonStyle::class)]
#[UsesClass(Config::class)]
#[UsesClass(StyleSet::class)]
#[UsesClass(Theme::class)]
class ActionStyleResolverTest extends TestCase
{
    private ActionStyleResolver $resolver;
    private ActionContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $this->resolver = new ActionStyleResolver();
        $this->context  = new ActionContext($request, $config);
    }

    public function test_it_puts_the_base_class_before_the_style(): void
    {
        $this->assertSame('btn btn-danger', $this->classes(new StyleSet(ButtonStyle::Danger)));
    }

    public function test_it_combines_several_styles_in_the_order_they_were_declared(): void
    {
        $this->assertSame(
            'btn btn-danger btn-link',
            $this->classes(new StyleSet(ButtonStyle::Danger, ButtonStyle::Link)),
        );
    }

    public function test_it_falls_back_to_the_default_style_of_the_region(): void
    {
        // Covers AE2.
        $this->assertSame('btn btn-primary', $this->classes(null));
    }

    public function test_the_default_style_case_falls_back_to_the_default_of_the_region(): void
    {
        // Covers AE3.
        $this->assertSame('btn btn-primary', $this->classes(new StyleSet(ButtonStyle::Default)));
    }

    public function test_it_skips_the_default_style_between_other_styles(): void
    {
        $this->assertSame(
            'btn btn-success',
            $this->classes(new StyleSet(ButtonStyle::Default, ButtonStyle::Success)),
        );
    }

    public function test_it_uses_the_dropdown_variant_inside_a_dropdown(): void
    {
        // Covers AE4.
        $this->assertSame(
            'dropdown-item text-danger',
            $this->classes(new StyleSet(ButtonStyle::Danger), ActionRegion::DropdownItem),
        );
    }

    public function test_a_dropdown_item_without_a_style_has_no_variant_and_no_trailing_space(): void
    {
        // Covers AE5.
        $this->assertSame('dropdown-item', $this->classes(null, ActionRegion::DropdownItem));
    }

    public function test_the_default_style_case_adds_nothing_inside_a_dropdown(): void
    {
        $this->assertSame(
            'dropdown-item',
            $this->classes(new StyleSet(ButtonStyle::Default), ActionRegion::DropdownItem),
        );
    }

    public function test_a_dropdown_toggle_keeps_the_button_variant(): void
    {
        $this->assertStringContainsString(
            'btn-danger',
            $this->classes(new StyleSet(ButtonStyle::Danger), ActionRegion::DropdownToggle),
        );
    }

    public function test_a_style_that_is_not_a_button_style_is_ignored(): void
    {
        $this->assertSame(
            'btn btn-danger',
            $this->classes(new StyleSet(TestStyle::First, ButtonStyle::Danger)),
        );
    }

    public function test_a_set_of_only_foreign_styles_falls_back_to_the_default(): void
    {
        $this->assertSame('btn btn-primary', $this->classes(new StyleSet(TestStyle::First)));
    }

    public function test_a_closure_decides_the_style_from_the_context(): void
    {
        $styles = new StyleSet(fn (ActionContext $context) => $context->isBulk
            ? ButtonStyle::Danger
            : ButtonStyle::Link);

        $this->assertSame('btn btn-link', $this->resolver->classes($styles, $this->context, ActionRegion::Button));
        $this->assertSame(
            'btn btn-danger',
            $this->resolver->classes($styles, $this->context->isBulk(), ActionRegion::Button),
        );
    }

    private function classes(?StyleSet $styles, ActionRegion $region = ActionRegion::Button): string
    {
        return $this->resolver->classes($styles, $this->context, $region);
    }
}
