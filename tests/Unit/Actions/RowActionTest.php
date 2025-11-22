<?php

declare(strict_types=1);

namespace Actions;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Actions\RowAction;
use BrickNPC\EloquentTables\Enums\ButtonStyle;

/**
 * @internal
 */
#[CoversClass(RowAction::class)]
#[CoversClass(Action::class)]
class RowActionTest extends TestCase
{
    public function test_it_sets_label_through_constructor_or_fluent_setter(): void
    {
        $action = new RowAction('#', 'Label');
        $this->assertSame('Label', $action->label);

        $action2 = new RowAction('#')->label('Label2');
        $this->assertSame('Label2', $action2->label);
    }

    public function test_it_sets_styles_through_constructor_or_fluent_setter(): void
    {
        $action = new RowAction(action: '#', styles: [ButtonStyle::DarkOutline]);
        $this->assertSame([ButtonStyle::DarkOutline], $action->styles);

        $action = new RowAction(action: '#')->styles(ButtonStyle::Dark);
        $this->assertSame([ButtonStyle::Dark], $action->styles);

        $action = new RowAction(action: '#', styles: [ButtonStyle::DarkOutline])->styles(ButtonStyle::Link);
        $this->assertSame([ButtonStyle::DarkOutline, ButtonStyle::Link], $action->styles);
    }

    public function test_it_sets_tooltip_through_constructor_or_fluent_setter(): void
    {
        $action = new RowAction(action: '#');
        $this->assertNull($action->tooltip);

        $action2 = new RowAction(action: '#', tooltip: 'tooltip');
        $this->assertSame('tooltip', $action2->tooltip);

        $action3 = new RowAction(action: '#')->tooltip('tooltip');
        $this->assertSame('tooltip', $action3->tooltip);

        $action4 = new RowAction(action: '#', tooltip: fn () => 'tooltip');
        $this->assertInstanceOf(\Closure::class, $action4->tooltip);

        $action5 = new RowAction(action: '#')->tooltip(fn () => 'tooltip');
        $this->assertInstanceOf(\Closure::class, $action5->tooltip);
    }

    public function test_it_sets_as_form_through_constructor_or_fluent_setter(): void
    {
        $action = new RowAction(action: '#');
        $this->assertFalse($action->asForm);
        $this->assertSame(Method::Post, $action->method);

        $action2 = new RowAction(action: '#', asForm: true);
        $this->assertTrue($action2->asForm);
        $this->assertSame(Method::Post, $action2->method);

        $action3 = new RowAction(action: '#')->asForm();
        $this->assertTrue($action3->asForm);
        $this->assertSame(Method::Post, $action3->method);
    }

    public function test_it_sets_method_through_constructor_or_fluent_setter(): void
    {
        $action = new RowAction(action: '#');
        $this->assertSame(Method::Post, $action->method);

        $action2 = new RowAction(action: '#', method: Method::Post);
        $this->assertSame(Method::Post, $action2->method);

        $action3 = new RowAction(action: '#')->asForm();
        $this->assertSame(Method::Post, $action3->method);

        $action4 = new RowAction(action: '#')->asForm(Method::Delete);
        $this->assertSame(Method::Delete, $action4->method);

        $action5 = new RowAction(action: '#')->method(Method::Delete);
        $this->assertSame(Method::Delete, $action5->method);
    }

    public function test_form_helper_methods_set_correct_method(): void
    {
        $action = new RowAction('#')->get();
        $this->assertTrue($action->asForm);
        $this->assertSame(Method::Get, $action->method);

        $action2 = new RowAction('#')->post();
        $this->assertTrue($action2->asForm);
        $this->assertSame(Method::Post, $action2->method);

        $action3 = new RowAction('#')->delete();
        $this->assertTrue($action3->asForm);
        $this->assertSame(Method::Delete, $action3->method);

        $action4 = new RowAction('#')->put();
        $this->assertTrue($action4->asForm);
        $this->assertSame(Method::Put, $action4->method);

        $action5 = new RowAction('#')->patch();
        $this->assertTrue($action5->asForm);
        $this->assertSame(Method::Patch, $action5->method);
    }

    public function test_it_sets_authorize_through_constructor_or_fluent_setter(): void
    {
        $action = new RowAction(action: '#', authorize: fn (Request $request, Model $model) => true);
        $this->assertInstanceOf(\Closure::class, $action->authorize);

        $action2 = new RowAction(action: '#')->authorize(fn (Request $request, Model $model) => true);
        $this->assertInstanceOf(\Closure::class, $action2->authorize);
    }

    public function test_it_sets_when_through_constructor_or_fluent_setter(): void
    {
        $action = new RowAction(action: '#', when: fn (Model $model) => true);
        $this->assertInstanceOf(\Closure::class, $action->when);

        $action2 = new RowAction(action: '#')->when(fn (Model $model) => true);
        $this->assertInstanceOf(\Closure::class, $action2->when);
    }

    public function test_it_sets_confirm_through_constructor_or_fluent_setter(): void
    {
        $action = new RowAction(action: '#', confirm: fn (Model $model) => true);
        $this->assertInstanceOf(\Closure::class, $action->confirm);

        $action2 = new RowAction(action: '#')->confirm(fn (Model $model) => true);
        $this->assertInstanceOf(\Closure::class, $action2->confirm);

        $action3 = new RowAction(action: '#', confirm: 'Are you sure? Test sentence!');
        $this->assertSame('Are you sure? Test sentence!', $action3->confirm);

        $action4 = new RowAction(action: '#')->confirm('Are you sure? Test sentence!');
        $this->assertSame('Are you sure? Test sentence!', $action4->confirm);
    }

    public function test_it_sets_confirm_value_through_constructor_or_fluent_setter(): void
    {
        $action = new RowAction(action: '#', confirmValue: 'test');
        $this->assertNull($action->confirm);
        $this->assertSame('test', $action->confirmValue);

        $action2 = new RowAction(action: '#')->confirm(confirmValue: 'test');
        $this->assertNotNull($action2->confirm);
        $this->assertSame('test', $action2->confirmValue);
    }

    public function test_providing_no_confirm_text_uses_default_text(): void
    {
        $action = new RowAction(action: '#')->confirm();
        $this->assertNotNull($action->confirm);
    }
}
