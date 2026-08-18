<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Enums;

use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use BrickNPC\EloquentTables\Enums\ActionCollectionType;

/**
 * @internal
 */
#[CoversClass(ActionCollectionType::class)]
class ActionCollectionTypeTest extends TestCase
{
    #[DataProvider('actionCollectionTypeProvider')]
    public function test_it_returns_correct_view(ActionCollectionType $type, string $expectedView): void
    {
        $this->assertSame($expectedView, $type->view());
    }

    public static function actionCollectionTypeProvider(): \Generator
    {
        yield [
            ActionCollectionType::Normal,
            'eloquent-tables::actions.collection.default',
        ];

        yield [
            ActionCollectionType::Grouped,
            'eloquent-tables::actions.collection.group',
        ];

        yield [
            ActionCollectionType::Dropdown,
            'eloquent-tables::actions.collection.dropdown',
        ];
    }

    public function test_it_has_a_type_for_every_collection_view(): void
    {
        $this->assertCount(3, ActionCollectionType::cases());
    }

    public function test_every_type_has_its_own_view(): void
    {
        $views = array_map(
            static fn (ActionCollectionType $type) => $type->view(),
            ActionCollectionType::cases(),
        );

        $this->assertSame($views, array_unique($views));
    }
}
