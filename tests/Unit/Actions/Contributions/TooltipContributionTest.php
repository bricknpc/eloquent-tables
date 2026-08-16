<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Actions\Contributions;

use Illuminate\Contracts\View\View;
use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer;
use BrickNPC\EloquentTables\Actions\Contributions\TooltipContribution;

/**
 * @internal
 */
#[CoversClass(TooltipContribution::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(CapabilityContribution::class)]
#[UsesClass(Config::class)]
#[UsesClass(LazyValue::class)]
#[UsesClass(RenderBuffer::class)]
#[UsesClass(Theme::class)]
class TooltipContributionTest extends TestCase
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

    public function test_tooltip_contribution_is_instance_of_capability_contribution(): void
    {
        $contribution = new TooltipContribution('Some text');

        $this->assertInstanceOf(CapabilityContribution::class, $contribution);
    }

    public function test_tooltip_contribution_is_final_class(): void
    {
        $reflection = new \ReflectionClass(TooltipContribution::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function test_render_attributes_returns_a_view(): void
    {
        $contribution = new TooltipContribution('Some text');

        $view = $contribution->renderAttributes($this->descriptor, $this->context);

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('eloquent-tables::actions.contribution.tooltip-attributes', $view->name());
    }

    public function test_render_attributes_passes_the_text_to_the_view(): void
    {
        $contribution = new TooltipContribution('Delete this user');

        $data = $contribution->renderAttributes($this->descriptor, $this->context)->getData();

        $this->assertSame('Delete this user', $data['text']);
    }

    public function test_render_attributes_passes_the_theme_and_data_namespace_to_the_view(): void
    {
        $contribution = new TooltipContribution('Some text');

        $data = $contribution->renderAttributes($this->descriptor, $this->context)->getData();

        $this->assertSame(Theme::Bootstrap5, $data['theme']);
        $this->assertSame('et', $data['dataNamespace']);
    }

    public function test_render_attributes_renders_the_bootstrap_tooltip_attributes(): void
    {
        $contribution = new TooltipContribution('Delete this user');

        $rendered = $contribution->renderAttributes($this->descriptor, $this->context)->render();

        $this->assertStringContainsString('data-bs-toggle="tooltip"', $rendered);
        $this->assertStringContainsString('data-bs-title="Delete this user"', $rendered);
    }

    public function test_render_attributes_escapes_the_text(): void
    {
        $contribution = new TooltipContribution('Delete "this" & that');

        $rendered = $contribution->renderAttributes($this->descriptor, $this->context)->render();

        $this->assertStringNotContainsString('data-bs-title="Delete "this"', $rendered);
        $this->assertStringContainsString('&quot;', $rendered);
        $this->assertStringContainsString('&amp;', $rendered);
    }

    public function test_render_attributes_handles_an_empty_text(): void
    {
        $contribution = new TooltipContribution('');

        $rendered = $contribution->renderAttributes($this->descriptor, $this->context)->render();

        $this->assertStringContainsString('data-bs-title=""', $rendered);
    }

    public function test_render_attributes_uses_a_stable_id_per_instance(): void
    {
        $contribution = new TooltipContribution('Some text');

        $first  = $contribution->renderAttributes($this->descriptor, $this->context)->getData();
        $second = $contribution->renderAttributes($this->descriptor, $this->context)->getData();

        $this->assertSame($first['id'], $second['id']);
    }

    public function test_every_contribution_gets_its_own_id(): void
    {
        $first  = new TooltipContribution('Some text');
        $second = new TooltipContribution('Some text');

        $this->assertNotSame(
            $first->renderAttributes($this->descriptor, $this->context)->getData()['id'],
            $second->renderAttributes($this->descriptor, $this->context)->getData()['id'],
        );
    }

    public function test_render_before_returns_null(): void
    {
        $contribution = new TooltipContribution('Some text');

        $this->assertNull($contribution->renderBefore($this->descriptor, $this->context));
    }

    public function test_render_after_returns_null(): void
    {
        $contribution = new TooltipContribution('Some text');

        $this->assertNull($contribution->renderAfter($this->descriptor, $this->context));
    }
}
