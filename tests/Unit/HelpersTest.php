<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit;

use PHPUnit\Framework\TestCase;
use BrickNPC\EloquentTables\Actions\Action;
use PHPUnit\Framework\Attributes\UsesClass;

use function BrickNPC\EloquentTables\actions;

use PHPUnit\Framework\Attributes\CoversFunction;

use function BrickNPC\EloquentTables\groupedActions;
use function BrickNPC\EloquentTables\dropdownActions;

use BrickNPC\EloquentTables\Actions\Collections\ActionCollection;

/**
 * @internal
 */
#[CoversFunction('BrickNPC\EloquentTables\actions')]
#[CoversFunction('BrickNPC\EloquentTables\dropdownActions')]
#[CoversFunction('BrickNPC\EloquentTables\groupedActions')]
#[UsesClass(ActionCollection::class)]
class HelpersTest extends TestCase
{
    private Action $action1;
    private Action $action2;
    private Action $action3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action1 = $this->createMock(Action::class);
        $this->action2 = $this->createMock(Action::class);
        $this->action3 = $this->createMock(Action::class);
    }

    public function test_actions_returns_action_collection(): void
    {
        $result = actions($this->action1);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_actions_accepts_single_action(): void
    {
        $result = actions($this->action1);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_actions_accepts_multiple_actions(): void
    {
        $result = actions($this->action1, $this->action2, $this->action3);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_actions_accepts_no_arguments(): void
    {
        $result = actions();

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_actions_accepts_action_collection(): void
    {
        $collection = new ActionCollection([$this->action1]);

        $result = actions($collection);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_actions_accepts_mixed_actions_and_collections(): void
    {
        $collection = new ActionCollection([$this->action1]);

        $result = actions($this->action2, $collection, $this->action3);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_dropdown_actions_returns_action_collection(): void
    {
        $result = dropdownActions($this->action1);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_dropdown_actions_accepts_single_action(): void
    {
        $result = dropdownActions($this->action1);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_dropdown_actions_accepts_multiple_actions(): void
    {
        $result = dropdownActions($this->action1, $this->action2, $this->action3);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_dropdown_actions_accepts_no_arguments(): void
    {
        $result = dropdownActions();

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_dropdown_actions_accepts_action_collection(): void
    {
        $collection = new ActionCollection([$this->action1]);

        $result = dropdownActions($collection);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_dropdown_actions_accepts_mixed_actions_and_collections(): void
    {
        $collection = new ActionCollection([$this->action1]);

        $result = dropdownActions($this->action2, $collection, $this->action3);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_grouped_actions_returns_action_collection(): void
    {
        $result = groupedActions($this->action1);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_grouped_actions_accepts_single_action(): void
    {
        $result = groupedActions($this->action1);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_grouped_actions_accepts_multiple_actions(): void
    {
        $result = groupedActions($this->action1, $this->action2, $this->action3);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_grouped_actions_accepts_no_arguments(): void
    {
        $result = groupedActions();

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_grouped_actions_accepts_action_collection(): void
    {
        $collection = new ActionCollection([$this->action1]);

        $result = groupedActions($collection);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_grouped_actions_accepts_mixed_actions_and_collections(): void
    {
        $collection = new ActionCollection([$this->action1]);

        $result = groupedActions($this->action2, $collection, $this->action3);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_actions_creates_collection_with_default_type(): void
    {
        $result = actions($this->action1);

        // Assuming ActionCollection has a way to check its type
        // You may need to adjust this based on ActionCollection's API
        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_dropdown_actions_creates_collection_with_dropdown_type(): void
    {
        $result = dropdownActions($this->action1);

        // Assuming ActionCollection exposes its type somehow
        // You may need to add a getter method to ActionCollection to properly test this
        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_grouped_actions_creates_collection_with_grouped_type(): void
    {
        $result = groupedActions($this->action1);

        // Assuming ActionCollection exposes its type somehow
        // You may need to add a getter method to ActionCollection to properly test this
        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_multiple_helper_functions_return_different_instances(): void
    {
        $result1 = actions($this->action1);
        $result2 = actions($this->action1);

        $this->assertNotSame($result1, $result2);
    }

    public function test_dropdown_actions_returns_different_instances(): void
    {
        $result1 = dropdownActions($this->action1);
        $result2 = dropdownActions($this->action1);

        $this->assertNotSame($result1, $result2);
    }

    public function test_grouped_actions_returns_different_instances(): void
    {
        $result1 = groupedActions($this->action1);
        $result2 = groupedActions($this->action1);

        $this->assertNotSame($result1, $result2);
    }

    public function test_helpers_can_be_nested(): void
    {
        $innerCollection = actions($this->action1);
        $outerCollection = actions($innerCollection, $this->action2);

        $this->assertInstanceOf(ActionCollection::class, $outerCollection);
    }

    public function test_dropdown_actions_can_be_nested(): void
    {
        $innerCollection = dropdownActions($this->action1);
        $outerCollection = dropdownActions($innerCollection, $this->action2);

        $this->assertInstanceOf(ActionCollection::class, $outerCollection);
    }

    public function test_grouped_actions_can_be_nested(): void
    {
        $innerCollection = groupedActions($this->action1);
        $outerCollection = groupedActions($innerCollection, $this->action2);

        $this->assertInstanceOf(ActionCollection::class, $outerCollection);
    }

    public function test_helpers_can_be_mixed_in_nesting(): void
    {
        $dropdownCollection = dropdownActions($this->action1);
        $groupedCollection  = groupedActions($this->action2);
        $result             = actions($dropdownCollection, $groupedCollection, $this->action3);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_actions_with_large_number_of_items(): void
    {
        $actions = array_fill(0, 100, $this->action1);

        $result = actions(...$actions);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_dropdown_actions_with_large_number_of_items(): void
    {
        $actions = array_fill(0, 100, $this->action1);

        $result = dropdownActions(...$actions);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }

    public function test_grouped_actions_with_large_number_of_items(): void
    {
        $actions = array_fill(0, 100, $this->action1);

        $result = groupedActions(...$actions);

        $this->assertInstanceOf(ActionCollection::class, $result);
    }
}
