@php
    use BrickNPC\EloquentTables\Actions\Action;use BrickNPC\EloquentTables\Actions\Collections\ActionCollection;
    use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
    use BrickNPC\EloquentTables\Actions\ActionRenderer;

    /** @var ActionCollection $actions */
    /** @var ActionContext $context */
    /** @var ActionRenderer $actionRenderer */
@endphp
<div aria-label="{{ $label }}">
    {!!
        $actions
            ->filter(fn(Action|ActionCollection $action) => $actionRenderer->canRender($action, $context))
            ->map(fn(Action|ActionCollection $action) => $actionRenderer->render($action, $context)?->render())
            ->implode(PHP_EOL)
    !!}
</div>
