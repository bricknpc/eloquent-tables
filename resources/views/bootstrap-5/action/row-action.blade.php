@if($asForm)
    <form method="{{ $method->getFormMethod() }}" action="{{ $action }}" name="row-action-{{ $id }}" id="row-action-{{ $id }}">
        @method($method->value)
        @csrf
    </form>
    {{-- The button is outside of the form tag because otherwise it won't render correctly in the btn-group --}}
    <button
        type="submit"
        class="btn {{ $styles }} d-flex align-items-center"
        form="row-action-{{ $id }}"
        @if($confirm)
            data-{{ $dataNamespace }}-confirm="{{ $confirm }}"
            @if($confirmValue)
                data-{{ $dataNamespace }}-confirm-value="{{ $confirmValue }}"
            @endif
        @endif
    >{{ $label }}</button>
@else
    <a
        href="{{ $action }}"
        class="btn {{ $styles }} d-flex align-items-center"
        @if($confirm)
            data-{{ $dataNamespace }}-confirm="{{ $confirm }}"
            @if($confirmValue)
                data-{{ $dataNamespace }}-confirm-value="{{ $confirmValue }}"
            @endif
        @endif
    >{{ $label }}</a>
@endif