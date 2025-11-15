<?php

declare(strict_types=1);

namespace Actions;

use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\TableAction;

/**
 * @internal
 */
#[CoversClass(TableAction::class)]
#[CoversClass(Action::class)]
class TableActionTest extends TestCase
{
    public function test_it_sets_label_through_constructor_or_fluent_setter(): void
    {
        $action = new TableAction('#', 'Label');
        $this->assertSame('Label', $action->label);

        $action2 = new TableAction('#')->label('Label2');
        $this->assertSame('Label2', $action2->label);
    }

    public function test_it_sets_styles_through_constructor_or_fluent_setter(): void
    {
        $action = new TableAction(action: '#', styles: [ButtonStyle::DarkOutline]);
        $this->assertSame([ButtonStyle::DarkOutline], $action->styles);

        $action = new TableAction(action: '#')->styles(ButtonStyle::Dark);
        $this->assertSame([ButtonStyle::Dark], $action->styles);

        $action = new TableAction(action: '#', styles: [ButtonStyle::DarkOutline])->styles(ButtonStyle::Link);
        $this->assertSame([ButtonStyle::DarkOutline, ButtonStyle::Link], $action->styles);
    }

    public function test_it_sets_as_modal_through_constructor_or_fluent_setter(): void
    {
        $action = new TableAction(action: '#');
        $this->assertFalse($action->asModal);

        $action2 = new TableAction(action: '#', asModal: true);
        $this->assertTrue($action2->asModal);

        $action3 = new TableAction(action: '#')->asModal();
        $this->assertTrue($action3->asModal);
    }
}
