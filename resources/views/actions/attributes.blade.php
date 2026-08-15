@php
    /** @var array<string, string> $attributes */
@endphp
{{-- The class attribute is rendered by the action itself, because it is combined with the classes of the theme. --}}
@foreach($attributes as $name => $value)
    @if($name !== 'class')
        {{ $name }}="{{ $value }}"
    @endif
@endforeach
