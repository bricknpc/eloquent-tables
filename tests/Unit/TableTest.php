<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit;

use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Enums\TableStyle;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use BrickNPC\EloquentTables\Builders\TableViewBuilder;
use BrickNPC\EloquentTables\Tests\Resources\TestTable;
use BrickNPC\EloquentTables\Factories\FormatterFactory;
use Symfony\Component\HttpKernel\Exception\HttpException;
use BrickNPC\EloquentTables\Builders\ColumnLabelViewBuilder;
use BrickNPC\EloquentTables\Builders\ColumnValueViewBuilder;
use BrickNPC\EloquentTables\Tests\Resources\TestTableAuthorisationFails;
use BrickNPC\EloquentTables\Tests\Resources\TestTableAuthorisationFailsCustomData;

/**
 * @internal
 */
#[CoversClass(Table::class)]
#[UsesClass(TableViewBuilder::class)]
#[UsesClass(ColumnLabelViewBuilder::class)]
#[UsesClass(ColumnValueViewBuilder::class)]
#[UsesClass(FormatterFactory::class)]
#[UsesClass(TableStyle::class)]
class TableTest extends TestCase
{
    public function test_default_authorisation_always_renders_the_table(): void
    {
        /** @var TestTable $table */
        $table = $this->app->make(TestTable::class);

        $rendered = $table->render();
        $invoked  = $table();
        $toString = (string) $table;

        $this->assertSame($rendered->name(), $invoked->name());
        $this->assertIsString($toString);
    }

    public function test_exception_is_thrown_when_authorization_fails(): void
    {
        /** @var TestTableAuthorisationFails $table */
        $table = $this->app->make(TestTableAuthorisationFails::class);

        $this->expectException(HttpException::class);

        $table->render();
    }

    public function test_exception_with_custom_message_and_custom_code_is_thrown_when_authorization_fails(): void
    {
        /** @var TestTableAuthorisationFailsCustomData $table */
        $table = $this->app->make(TestTableAuthorisationFailsCustomData::class);

        try {
            $table->render();
        } catch (HttpException $e) {
            $this->assertSame('This is a custom message.', $e->getMessage());
            $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getStatusCode());
        }
    }
}
