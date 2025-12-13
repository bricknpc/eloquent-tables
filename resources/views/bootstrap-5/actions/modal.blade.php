@php
    use BrickNPC\EloquentTables\Enums\Method;
    use BrickNPC\EloquentTables\Actions\Intents\Modal as ActionIntent;

    /** @var ActionIntent $intent */
@endphp

@if ($intent->payload['method'] === Method::Get)
    <a href="{{ $intent->payload['url']->resolve($context) }}"
       class="btn btn-primary"
    >{!! $label !!}</a>
@else
    Form button
@endif
