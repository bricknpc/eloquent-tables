@php
    use BrickNPC\EloquentTables\Actions\Intents\HttpModal as ActionIntent;

    /** @var ActionIntent $intent */
@endphp

{!! $beforeContent->render() !!}

<button type="button"
        @if($context->asDropdown)
            class="{{ trim('dropdown-item ' . ($attributes['class'] ?? '')) }}"
        @else
            class="{{ trim('btn ' . ($attributes['class'] ?? 'btn-primary')) }}"
        @endif
        data-bs-toggle="modal"
        data-bs-target="#modal-{{ $id }}"
        @include('eloquent-tables::actions.attributes')
        {!! $renderedAttributes->render() !!}
>{!! $label !!}</button>

<div class="modal fade"
     id="modal-{{ $id }}"
     tabindex="-1"
     aria-labelledby="modal-{{ $id }}-title"
     aria-hidden="true"
     data-{{ $dataNamespace }}-modal-frame="true"
>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-{{ $id }}-title">{!! $intent->title()->resolve($context) !!}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body p-0">
                <div class="d-flex justify-content-center p-4" data-{{ $dataNamespace }}-modal-loading="true">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">{{ __('Loading') }}</span>
                    </div>
                </div>
                <iframe class="w-100 border-0"
                        style="height: 60vh;"
                        title="{{ strip_tags((string) $intent->title()->resolve($context)) }}"
                        data-{{ $dataNamespace }}-modal-src="{{ $intent->url()->resolve($context) }}"
                        hidden
                ></iframe>
                <div class="alert alert-danger m-3" data-{{ $dataNamespace }}-modal-error="true" hidden>
                    {{ __('The content of this modal could not be loaded.') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

{!! $afterContent->render() !!}
