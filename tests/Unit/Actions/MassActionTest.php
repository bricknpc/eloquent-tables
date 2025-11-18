<?php

declare(strict_types=1);

namespace Actions;

use Illuminate\Support\HtmlString;
use BrickNPC\EloquentTables\Enums\Method;
use Illuminate\Contracts\Support\Htmlable;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use BrickNPC\EloquentTables\Actions\MassAction;

/**
 * @internal
 */
#[CoversClass(MassAction::class)]
class MassActionTest extends TestCase
{
    #[DataProvider('labelProvider')]
    public function test_it_sets_label_through_constructor_or_fluent_setter(Htmlable|string|\Stringable $label): void
    {
        $action = new MassAction('#', $label);
        $this->assertSame($label, $action->label);

        $action2 = new MassAction('#')->label($label);
        $this->assertSame($label, $action2->label);
    }

    public static function labelProvider(): \Generator
    {
        yield [
            'label as string',
        ];

        yield [
            new HtmlString('label as html string'),
        ];

        yield [
            new class implements \Stringable {
                public function __toString(): string
                {
                    return 'label as stringable';
                }
            },
        ];
    }

    #[DataProvider('methodsProvider')]
    public function test_it_sets_method_through_constructor_or_fluent_setter(Method $method): void
    {
        $action = new MassAction('#', 'Label', method: $method);
        $this->assertSame($method, $action->method);

        $action2 = new MassAction('#')->method($method);
        $this->assertSame($method, $action2->method);
    }

    public static function methodsProvider(): \Generator
    {
        yield [
            Method::Get,
        ];

        yield [
            Method::Post,
        ];

        yield [
            Method::Put,
        ];

        yield [
            Method::Patch,
        ];

        yield [
            Method::Delete,
        ];
    }

    public function test_method_helper_methods_set_correct_method(): void
    {
        $action = new MassAction('#')->get();
        $this->assertSame(Method::Get, $action->method);

        $action2 = new MassAction('#')->post();
        $this->assertSame(Method::Post, $action2->method);

        $action3 = new MassAction('#')->put();
        $this->assertSame(Method::Put, $action3->method);

        $action4 = new MassAction('#')->patch();
        $this->assertSame(Method::Patch, $action4->method);

        $action5 = new MassAction('#')->delete();
        $this->assertSame(Method::Delete, $action5->method);
    }

    public function test_it_sets_authorize_through_constructor_or_fluent_setter(): void
    {
        $action = new MassAction('#', 'Label', authorize: fn () => true);
        $this->assertInstanceOf(\Closure::class, $action->authorize);

        $action2 = new MassAction('#')->authorize(fn () => true);
        $this->assertInstanceOf(\Closure::class, $action2->authorize);
    }

    public function test_it_sets_confirm_through_constructor_or_fluent_setter(): void
    {
        $action = new MassAction('#', 'Label', confirm: 'Confirm text', confirmValue: 'Confirm value');
        $this->assertSame('Confirm text', $action->confirm);
        $this->assertSame('Confirm value', $action->confirmValue);

        $action2 = new MassAction('#')->confirm('Confirm text', 'Confirm value');
        $this->assertSame('Confirm text', $action2->confirm);
        $this->assertSame('Confirm value', $action2->confirmValue);

        $action3 = new MassAction('#');
        $this->assertNull($action3->confirm);
        $this->assertNull($action3->confirmValue);
    }

    public function test_it_sets_tooltip_through_constructor_or_fluent_setter(): void
    {
        $action = new MassAction('#', 'Label', tooltip: 'Tooltip text');
        $this->assertSame('Tooltip text', $action->tooltip);

        $action2 = new MassAction('#')->tooltip('Tooltip text');
        $this->assertSame('Tooltip text', $action2->tooltip);
    }
}
