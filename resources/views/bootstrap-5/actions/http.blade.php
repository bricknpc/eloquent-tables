@php
    use BrickNPC\EloquentTables\Enums\Method;
    use BrickNPC\EloquentTables\Actions\Intents\Http as ActionIntent;

    /** @var ActionIntent $intent
 */
@endphp

{!! $beforeContent->render() !!}

@if ($intent->method === Method::Get)
    <a href="{{ $intent->url()->resolve($context) }}"
       @if($context->asDropdown)
           class="dropdown-item"
       @else
           class="btn btn-primary"
            @endif
            {!! $renderedAttributes->render() !!}
    >{!! $label !!}</a>
@else
    <button type="submit"
            @if($context->asDropdown)
                class="dropdown-item"
            @else
                class="btn btn-primary"
            @endif
            {!! $renderedAttributes->render() !!}
            form="{{ $id }}"
    >
        {!! $label !!}
        {{-- This is not valid HTML, but it works. We need the button to be the outer element for use in btn-groups and dropdowns. --}}
        <form id="{{ $id }}" name="{{ $id }}" action="{{ $intent->url()->resolve($context) }}" method="POST">
            @csrf
            @method($intent->method->value)
        </form>
    </button>
@endif

{!! $afterContent->render() !!}
