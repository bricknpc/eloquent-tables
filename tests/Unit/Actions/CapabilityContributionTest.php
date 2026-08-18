<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Actions;

use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer;

/**
 * @internal
 */
#[CoversClass(CapabilityContribution::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(Config::class)]
#[UsesClass(LazyValue::class)]
#[UsesClass(RenderBuffer::class)]
class CapabilityContributionTest extends TestCase
{
    private ActionDescriptor $descriptor;
    private ActionContext $context;
    private CapabilityContribution $contribution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->descriptor = new ActionDescriptor();

        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $this->context = new ActionContext($request, $config);

        $this->contribution = new class extends CapabilityContribution {};
    }

    public function test_capability_contribution_is_abstract(): void
    {
        $reflection = new \ReflectionClass(CapabilityContribution::class);

        $this->assertTrue($reflection->isAbstract());
    }

    public function test_render_before_returns_null_by_default(): void
    {
        $this->assertNull($this->contribution->renderBefore($this->descriptor, $this->context));
    }

    public function test_render_attributes_returns_null_by_default(): void
    {
        $this->assertNull($this->contribution->renderAttributes($this->descriptor, $this->context));
    }

    public function test_render_after_returns_null_by_default(): void
    {
        $this->assertNull($this->contribution->renderAfter($this->descriptor, $this->context));
    }

    public function test_default_contributions_add_nothing_to_a_render_buffer(): void
    {
        $buffer = new RenderBuffer();

        $buffer->add($this->contribution->renderBefore($this->descriptor, $this->context));
        $buffer->add($this->contribution->renderAttributes($this->descriptor, $this->context));
        $buffer->add($this->contribution->renderAfter($this->descriptor, $this->context));

        $this->assertSame('', $buffer->render());
    }

    public function test_subclasses_can_override_a_single_render_method(): void
    {
        $contribution = new class extends CapabilityContribution {
            public function renderAttributes(ActionDescriptor $descriptor, ActionContext $context): string
            {
                return 'data-custom="true"';
            }
        };

        $this->assertNull($contribution->renderBefore($this->descriptor, $this->context));
        $this->assertSame('data-custom="true"', $contribution->renderAttributes($this->descriptor, $this->context));
        $this->assertNull($contribution->renderAfter($this->descriptor, $this->context));
    }
}
