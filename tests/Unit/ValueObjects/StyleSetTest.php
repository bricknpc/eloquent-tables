<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\ValueObjects;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Tests\Resources\TestStyle;
use BrickNPC\EloquentTables\Styles\Contexts\RowContext;

/**
 * @internal
 */
#[CoversClass(StyleSet::class)]
#[UsesClass(RowContext::class)]
class StyleSetTest extends TestCase
{
    public function test_static_styles_resolve_to_themselves(): void
    {
        $set = new StyleSet(TestStyle::First, TestStyle::Second);

        $this->assertSame([TestStyle::First, TestStyle::Second], $set->resolve($this->context()));
    }

    public function test_an_empty_set_resolves_to_nothing(): void
    {
        $this->assertSame([], new StyleSet()->resolve($this->context()));
    }

    public function test_a_closure_returning_one_style_merges_it(): void
    {
        $set = new StyleSet(TestStyle::First, static fn () => TestStyle::Second);

        $this->assertSame([TestStyle::First, TestStyle::Second], $set->resolve($this->context()));
    }

    public function test_a_closure_returning_a_list_merges_all_of_them(): void
    {
        $set = new StyleSet(TestStyle::First, static fn () => [TestStyle::Second, TestStyle::Third]);

        $this->assertSame(
            [TestStyle::First, TestStyle::Second, TestStyle::Third],
            $set->resolve($this->context()),
        );
    }

    public function test_a_closure_returning_null_leaves_the_static_styles_alone(): void
    {
        $set = new StyleSet(TestStyle::First, static fn () => null);

        $this->assertSame([TestStyle::First], $set->resolve($this->context()));
    }

    public function test_a_closure_may_be_declared_before_the_static_styles(): void
    {
        $set = new StyleSet(static fn () => TestStyle::Second, TestStyle::First);

        $this->assertSame([TestStyle::First, TestStyle::Second], $set->resolve($this->context()));
    }

    public function test_a_colliding_style_is_kept_rather_than_resolved(): void
    {
        // Covers AE6.
        $set = new StyleSet(TestStyle::First, static fn () => TestStyle::First);

        $this->assertSame([TestStyle::First, TestStyle::First], $set->resolve($this->context()));
    }

    public function test_every_closure_contributes(): void
    {
        $set = new StyleSet(static fn () => TestStyle::First, static fn () => TestStyle::Second);

        $this->assertSame([TestStyle::First, TestStyle::Second], $set->resolve($this->context()));
    }

    public function test_the_closure_receives_the_context(): void
    {
        $received = null;
        $context  = $this->context();

        new StyleSet(static function ($given) use (&$received) {
            $received = $given;

            return null;
        })->resolve($context);

        $this->assertSame($context, $received);
    }

    public function test_a_closure_returning_something_that_is_not_a_style_is_ignored(): void
    {
        $set = new StyleSet(TestStyle::First, static fn () => ['not a style', TestStyle::Second]);

        $this->assertSame([TestStyle::First, TestStyle::Second], $set->resolve($this->context()));
    }

    public function test_with_appends_to_an_existing_set(): void
    {
        $set = new StyleSet(TestStyle::First)->with(TestStyle::Second, static fn () => TestStyle::Third);

        $this->assertSame(
            [TestStyle::First, TestStyle::Second, TestStyle::Third],
            $set->resolve($this->context()),
        );
    }

    private function context(): RowContext
    {
        /** @var Request $request */
        $request = $this->app->make('request');

        return new RowContext($request, new TestModel());
    }
}
