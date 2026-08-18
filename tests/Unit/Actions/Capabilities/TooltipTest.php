<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Actions\Capabilities;

use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Capabilities\Tooltip;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer;
use BrickNPC\EloquentTables\Actions\Contributions\TooltipContribution;

/**
 * @internal
 */
#[CoversClass(Tooltip::class)]
#[UsesClass(ActionCapability::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(CapabilityContribution::class)]
#[UsesClass(TooltipContribution::class)]
#[UsesClass(Config::class)]
#[UsesClass(LazyValue::class)]
#[UsesClass(RenderBuffer::class)]
#[UsesClass(Theme::class)]
class TooltipTest extends TestCase
{
    private ActionDescriptor $descriptor;
    private ActionContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->descriptor = new ActionDescriptor();

        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $this->context = new ActionContext($request, $config);
    }

    public function test_tooltip_is_instance_of_action_capability(): void
    {
        $tooltip = new Tooltip('Some text');

        $this->assertInstanceOf(ActionCapability::class, $tooltip);
    }

    public function test_tooltip_is_final_class(): void
    {
        $reflection = new \ReflectionClass(Tooltip::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function test_contribute_returns_a_tooltip_contribution(): void
    {
        $tooltip = new Tooltip('Some text');

        $contribution = $tooltip->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(TooltipContribution::class, $contribution);
    }

    public function test_contribute_uses_the_string_text(): void
    {
        $tooltip = new Tooltip('Delete this user');

        $contribution = $tooltip->contribute($this->descriptor, $this->context);

        $this->assertStringContainsString(
            'data-bs-title="Delete this user"',
            $contribution->renderAttributes($this->descriptor, $this->context)->render(),
        );
    }

    public function test_contribute_resolves_a_closure_text_with_the_context(): void
    {
        $tooltip = new Tooltip(static fn(ActionContext $context) => $context->isBulk ? 'Bulk tooltip' : 'Row tooltip');

        $contribution = $tooltip->contribute($this->descriptor, $this->context);

        $this->assertStringContainsString(
            'data-bs-title="Row tooltip"',
            $contribution->renderAttributes($this->descriptor, $this->context)->render(),
        );
    }

    public function test_contribute_resolves_the_closure_against_the_given_context(): void
    {
        $tooltip = new Tooltip(static fn(ActionContext $context) => $context->isBulk ? 'Bulk tooltip' : 'Row tooltip');

        $contribution = $tooltip->contribute($this->descriptor, $this->context->isBulk());

        $this->assertStringContainsString(
            'data-bs-title="Bulk tooltip"',
            $contribution->renderAttributes($this->descriptor, $this->context)->render(),
        );
    }

    public function test_contribute_passes_the_context_to_the_closure(): void
    {
        $contextPassed = null;

        $tooltip = new Tooltip(static function (ActionContext $ctx) use (&$contextPassed): string {
            $contextPassed = $ctx;

            return 'Some text';
        });

        $tooltip->contribute($this->descriptor, $this->context);

        $this->assertSame($this->context, $contextPassed);
    }

    public function test_contribute_falls_back_to_an_empty_text_when_the_closure_returns_null(): void
    {
        /** @var \Closure(ActionContext $context): string $closure */
        $closure = static fn(ActionContext $context) => null;

        $tooltip = new Tooltip($closure);

        $contribution = $tooltip->contribute($this->descriptor, $this->context);

        $this->assertStringContainsString(
            'data-bs-title=""',
            $contribution->renderAttributes($this->descriptor, $this->context)->render(),
        );
    }

    public function test_contribute_returns_a_new_contribution_on_every_call(): void
    {
        $tooltip = new Tooltip('Some text');

        $first  = $tooltip->contribute($this->descriptor, $this->context);
        $second = $tooltip->contribute($this->descriptor, $this->context);

        $this->assertNotSame($first, $second);
    }

    public function test_check_returns_true(): void
    {
        $tooltip = new Tooltip('Some text');

        $this->assertTrue($tooltip->check($this->descriptor, $this->context));
    }

    public function test_apply_does_not_change_the_descriptor(): void
    {
        $tooltip = new Tooltip('Some text');

        $tooltip->apply($this->descriptor, $this->context);

        $this->assertSame([], $this->descriptor->attributes);
        $this->assertNull($this->descriptor->intent);
        $this->assertSame('', $this->descriptor->attributesRender->render());
    }
}
