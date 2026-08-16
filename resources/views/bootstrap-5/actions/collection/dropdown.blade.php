@php
    use BrickNPC\EloquentTables\Actions\Action;use BrickNPC\EloquentTables\Actions\Collections\ActionCollection;
    use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
    use BrickNPC\EloquentTables\Actions\ActionRenderer;

    /** @var ActionCollection $actions */
    /** @var ActionContext $context */
    /** @var ActionRenderer $actionRenderer */
@endphp
<div class="dropdown">
    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        {{ $label }}
    </button>
    <ul class="dropdown-menu">
        @foreach ($actions as $action)
            @if (!$actionRenderer->canRender($action, $context))
                @continue
            @endif
            <li>{{ $actionRenderer->render($action, $context->asDropdown()) }}</li>
        @endforeach
    </ul>
</div>
