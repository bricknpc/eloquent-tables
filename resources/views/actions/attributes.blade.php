@php
    /** @var array<string, string> $attributes */
@endphp
@foreach($attributes as $name => $value)
    {{ $name }}="{{ $value }}"
@endforeach
