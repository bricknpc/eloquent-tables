<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit;

use PHPUnit\Framework\TestCase;
use BrickNPC\EloquentTables\Actions\Action;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversFunction;
use BrickNPC\EloquentTables\Enums\ActionCollectionType;
use BrickNPC\EloquentTables\Actions\Collections\ActionCollection;

use function BrickNPC\EloquentTables\actions;

/**
 * @internal
 */
#[CoversFunction('BrickNPC\EloquentTables\actions')]
#[CoversFunction('BrickNPC\EloquentTables\dropdownActions')]
#[CoversFunction('BrickNPC\EloquentTables\groupedActions')]
#[UsesClass(ActionCollection::class)]
#[UsesClass(ActionCollectionType::class)]
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

    /**
     * The only thing that distinguishes the three helpers is the type of the collection they build.
     *
     * @param callable-string $helper
     */
    #[DataProvider('helperProvider')]
    public function test_it_builds_a_collection_of_the_matching_type(
        string $helper,
        ActionCollectionType $expected,
    ): void {
        $this->assertSame($expected, $helper($this->action1)->type);
    }

    /**
     * @param callable-string $helper
     */
    #[DataProvider('helperProvider')]
    public function test_it_collects_every_action_in_order(string $helper, ActionCollectionType $expected): void
    {
        $result = $helper($this->action1, $this->action2, $this->action3);

        $this->assertCount(3, $result);
        $this->assertSame([$this->action1, $this->action2, $this->action3], $result->all());
    }

    /**
     * @param callable-string $helper
     */
    #[DataProvider('helperProvider')]
    public function test_it_builds_an_empty_collection_without_arguments(
        string $helper,
        ActionCollectionType $expected,
    ): void {
        $result = $helper();

        $this->assertCount(0, $result);
        $this->assertSame($expected, $result->type);
    }

    /**
     * @param callable-string $helper
     */
    #[DataProvider('helperProvider')]
    public function test_it_accepts_collections_alongside_actions(string $helper, ActionCollectionType $expected): void
    {
        $nested = new ActionCollection([$this->action1]);

        $result = $helper($this->action2, $nested, $this->action3);

        $this->assertCount(3, $result);
        $this->assertSame($nested, $result->all()[1]);
    }

    /**
     * @param callable-string $helper
     */
    #[DataProvider('helperProvider')]
    public function test_it_returns_a_new_collection_on_every_call(string $helper, ActionCollectionType $expected): void
    {
        $this->assertNotSame($helper($this->action1), $helper($this->action1));
    }

    /**
     * @param callable-string $helper
     */
    #[DataProvider('helperProvider')]
    public function test_a_nested_collection_keeps_its_own_type(string $helper, ActionCollectionType $expected): void
    {
        $outer = actions($helper($this->action1), $this->action2);

        /** @var ActionCollection $inner */
        $inner = $outer->all()[0];

        $this->assertSame(ActionCollectionType::Normal, $outer->type);
        $this->assertSame($expected, $inner->type);
    }

    /**
     * @return \Generator<string, array{callable-string, ActionCollectionType}>
     */
    public static function helperProvider(): \Generator
    {
        yield 'actions' => ['BrickNPC\EloquentTables\actions', ActionCollectionType::Normal];

        yield 'dropdownActions' => ['BrickNPC\EloquentTables\dropdownActions', ActionCollectionType::Dropdown];

        yield 'groupedActions' => ['BrickNPC\EloquentTables\groupedActions', ActionCollectionType::Grouped];
    }
}
