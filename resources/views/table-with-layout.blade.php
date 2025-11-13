@php
    use BrickNPC\EloquentTables\Enums\Theme;
    use BrickNPC\EloquentTables\Attributes\Layout;

    /** @var Theme $theme */
    /** @var Layout $layout */
@endphp

@extends($layout->name, $layout->with)

@section($layout->section ?? 'slot')
    @include('eloquent-tables::' . $theme->value . '.table')
@endsection