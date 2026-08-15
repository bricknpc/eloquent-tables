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
use BrickNPC\EloquentTables\Actions\Contributions\ConfirmationContribution;

/**
 * @internal
 */
#[CoversClass(ConfirmationContribution::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(CapabilityContribution::class)]
#[UsesClass(Config::class)]
#[UsesClass(LazyValue::class)]
#[UsesClass(RenderBuffer::class)]
#[UsesClass(Theme::class)]
class ConfirmationContributionTest extends TestCase
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

    public function test_confirmation_contribution_is_instance_of_capability_contribution(): void
    {
        $contribution = new ConfirmationContribution('Are you sure?');

        $this->assertInstanceOf(CapabilityContribution::class, $contribution);
    }

    public function test_confirmation_contribution_is_final_class(): void
    {
        $reflection = new \ReflectionClass(ConfirmationContribution::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function test_render_attributes_returns_a_view(): void
    {
        $contribution = new ConfirmationContribution('Are you sure?');

        $view = $contribution->renderAttributes($this->descriptor, $this->context);

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('eloquent-tables::actions.contribution.confirmation-attributes', $view->name());
    }

    public function test_render_attributes_renders_the_confirm_attributes(): void
    {
        $contribution = new ConfirmationContribution('Are you sure?');

        $rendered = $contribution->renderAttributes($this->descriptor, $this->context)->render();

        $this->assertStringContainsString('data-et-confirm="true"', $rendered);
        $this->assertStringContainsString('data-et-confirm-target="#confirm-', $rendered);
    }

    public function test_render_attributes_uses_the_configured_data_namespace(): void
    {
        $this->app->make('config')->set('eloquent-tables.data-namespace', 'tables');

        $contribution = new ConfirmationContribution('Are you sure?');

        $rendered = $contribution->renderAttributes($this->descriptor, $this->context)->render();

        $this->assertStringContainsString('data-tables-confirm="true"', $rendered);
        $this->assertStringNotContainsString('data-et-confirm="true"', $rendered);
    }

    public function test_render_attributes_omits_the_confirm_value_attributes_when_no_understand_value_is_given(): void
    {
        $contribution = new ConfirmationContribution('Are you sure?');

        $rendered = $contribution->renderAttributes($this->descriptor, $this->context)->render();

        $this->assertStringNotContainsString('data-et-confirm-value=', $rendered);
        $this->assertStringNotContainsString('data-et-confirm-value-input=', $rendered);
    }

    public function test_render_attributes_adds_the_confirm_value_attributes_when_an_understand_value_is_given(): void
    {
        $contribution = new ConfirmationContribution('Are you sure?', understandValue: 'DELETE');

        $rendered = $contribution->renderAttributes($this->descriptor, $this->context)->render();

        $this->assertStringContainsString('data-et-confirm-value="DELETE"', $rendered);
        $this->assertStringContainsString('data-et-confirm-value-input="confirm-value-', $rendered);
    }

    public function test_render_attributes_does_not_mark_the_action_as_a_bulk_form(): void
    {
        $contribution = new ConfirmationContribution('Are you sure?');

        $rendered = $contribution->renderAttributes($this->descriptor, $this->context->isBulk())->render();

        $this->assertStringNotContainsString('bulk-action-form', $rendered);
    }

    public function test_render_attributes_are_the_same_for_a_bulk_context(): void
    {
        $contribution = new ConfirmationContribution('Are you sure?');

        $normal = $contribution->renderAttributes($this->descriptor, $this->context)->render();
        $bulk   = $contribution->renderAttributes($this->descriptor, $this->context->isBulk())->render();

        $this->assertSame($normal, $bulk);
    }

    public function test_render_after_returns_a_view(): void
    {
        $contribution = new ConfirmationContribution('Are you sure?');

        $view = $contribution->renderAfter($this->descriptor, $this->context);

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('eloquent-tables::actions.contribution.confirmation-modal', $view->name());
    }

    public function test_render_after_renders_the_confirmation_text(): void
    {
        $contribution = new ConfirmationContribution('Are you sure you want to delete this user?');

        $rendered = $contribution->renderAfter($this->descriptor, $this->context)->render();

        $this->assertStringContainsString('Are you sure you want to delete this user?', $rendered);
    }

    public function test_render_after_uses_the_default_button_labels(): void
    {
        $contribution = new ConfirmationContribution('Are you sure?');

        $rendered = $contribution->renderAfter($this->descriptor, $this->context)->render();

        $this->assertStringContainsString('Cancel', $rendered);
        $this->assertStringContainsString('Yes, confirm', $rendered);
    }

    public function test_render_after_uses_the_custom_button_labels(): void
    {
        $contribution = new ConfirmationContribution('Are you sure?', 'Delete it', 'Keep it');

        $rendered = $contribution->renderAfter($this->descriptor, $this->context)->render();

        $this->assertStringContainsString('Delete it', $rendered);
        $this->assertStringContainsString('Keep it', $rendered);
        $this->assertStringNotContainsString('Yes, confirm', $rendered);
    }

    public function test_render_after_omits_the_confirm_value_input_when_no_understand_value_is_given(): void
    {
        $contribution = new ConfirmationContribution('Are you sure?');

        $rendered = $contribution->renderAfter($this->descriptor, $this->context)->render();

        $this->assertStringNotContainsString('name="confirm-value"', $rendered);
    }

    public function test_render_after_adds_the_confirm_value_input_when_an_understand_value_is_given(): void
    {
        $contribution = new ConfirmationContribution('Are you sure?', understandValue: 'DELETE');

        $rendered = $contribution->renderAfter($this->descriptor, $this->context)->render();

        $this->assertStringContainsString('name="confirm-value"', $rendered);
        $this->assertStringContainsString('<code>DELETE</code>', $rendered);
    }

    public function test_the_modal_id_matches_the_confirm_target_attribute(): void
    {
        $contribution = new ConfirmationContribution('Are you sure?');

        $id = $contribution->renderAttributes($this->descriptor, $this->context)->getData()['id'];

        $attributes = $contribution->renderAttributes($this->descriptor, $this->context)->render();
        $modal      = $contribution->renderAfter($this->descriptor, $this->context)->render();

        $this->assertStringContainsString('data-et-confirm-target="#confirm-' . $id . '"', $attributes);
        $this->assertStringContainsString('id="confirm-' . $id . '"', $modal);
    }

    public function test_every_contribution_gets_its_own_id(): void
    {
        $first  = new ConfirmationContribution('Are you sure?');
        $second = new ConfirmationContribution('Are you sure?');

        $this->assertNotSame(
            $first->renderAttributes($this->descriptor, $this->context)->getData()['id'],
            $second->renderAttributes($this->descriptor, $this->context)->getData()['id'],
        );
    }

    public function test_render_before_returns_null(): void
    {
        $contribution = new ConfirmationContribution('Are you sure?');

        $this->assertNull($contribution->renderBefore($this->descriptor, $this->context));
    }
}
