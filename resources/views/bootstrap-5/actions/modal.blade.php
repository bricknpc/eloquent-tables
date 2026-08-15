@php
    use BrickNPC\EloquentTables\Actions\Intents\Modal as ActionIntent;

    /** @var ActionIntent $intent */
@endphp

{!! $beforeContent->render() !!}

<button type="button"
        @if($context->asDropdown)
            class="dropdown-item"
        @else
            class="btn btn-primary"
        @endif
        data-bs-toggle="modal"
        data-bs-target="#modal-{{ $id }}"
        {!! $renderedAttributes->render() !!}
>{!! $label !!}</button>

<div class="modal fade" id="modal-{{ $id }}" tabindex="-1" aria-labelledby="modal-{{ $id }}-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-{{ $id }}-title">{!! $intent->title()->resolve($context) !!}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                {!! $intent->content()->resolve($context) !!}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

{!! $afterContent->render() !!}
