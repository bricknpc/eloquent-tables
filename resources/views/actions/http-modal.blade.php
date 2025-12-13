@php
    use BrickNPC\EloquentTables\Enums\Theme;

    /** @var Theme $theme */
@endphp
@include('eloquent-tables::' . $theme->value . '.actions.http-modal')