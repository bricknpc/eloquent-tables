@if($asForm)
    <form method="{{ $method }}" action="{{ $action }}" name="row-action-{{ $id }}" id="row-action-{{ $id }}">
        @method($method)
        <button
            type="submit"
            class="btn {{ $styles }}"
            form="row-action-{{ $id }}"
            @if($confirm)
                data-{{ $dataNamespace }}-confirm="{{ $confirm }}"
                @if($confirmValue)
                    data-{{ $dataNamespace }}-confirm-value="{{ $confirmValue }}"
                @endif
            @endif
        >{{ $label }}</button>
    </form>
@else
    <a
        href="{{ $action }}"
        class="btn {{ $styles }}"
        @if($confirm)
            data-{{ $dataNamespace }}-confirm="{{ $confirm }}"
            @if($confirmValue)
                data-{{ $dataNamespace }}-confirm-value="{{ $confirmValue }}"
            @endif
        @endif
    >{{ $label }}</a>
@endif