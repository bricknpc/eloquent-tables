@if($confirm)
    <div class="modal" id="row-action-{{ $id }}-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <p>{{ $confirm }}</p>
                    @if($confirmValue)
                        <p>
                            {{ new \Illuminate\Support\HtmlString(__('To confirm this action, please type the text <code>:confirmValue</code> into the textbox below.', ['confirmValue' => $confirmValue])) }}
                        </p>
                        <p>
                            <input type="text" name="confirm-value" id="confirm-value-row-{{ $id }}" class="form-control" />
                        </p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" data-{{ $dataNamespace }}-cancel-confirm="true">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary" data-{{ $dataNamespace }}-confirm-submit="true">{{ __('Yes, confirm') }}</button>
                </div>
            </div>
        </div>
    </div>
@endif
@if($asForm)
    <form
        method="{{ $method->getFormMethod() }}"
        action="{{ $action }}"
        name="row-action-{{ $id }}"
        id="row-action-{{ $id }}"
        @if($confirm)
            data-{{ $dataNamespace }}-confirm="true"
            data-{{ $dataNamespace }}-confirm-target="#row-action-{{ $id }}-modal"
            @if($confirmValue)
                data-{{ $dataNamespace }}-confirm-value="{{ $confirmValue }}"
                data-{{ $dataNamespace }}-confirm-value-input="confirm-value-row-{{ $id }}"
            @endif
        @endif
    >
        @method($method->value)
        @csrf
    </form>
    {{-- The button is outside of the form tag because otherwise it won't render correctly in the btn-group --}}
    <button
        type="submit"
        class="btn {{ $styles }} d-flex align-items-center"
        form="row-action-{{ $id }}"
        @if($tooltip)
            data-bs-toggle="tooltip"
            data-bs-title="{{ $tooltip }}"
        @endif
    >{{ $label }}</button>
@else
    <a
        href="{{ $action }}"
        class="btn {{ $styles }} d-flex align-items-center"
        @if($confirm)
            data-{{ $dataNamespace }}-confirm="true"
            data-{{ $dataNamespace }}-confirm-target="#row-action-{{ $id }}-modal"
            @if($confirmValue)
                data-{{ $dataNamespace }}-confirm-value="{{ $confirmValue }}"
                data-{{ $dataNamespace }}-confirm-value-input="confirm-value-row-{{ $id }}"
            @endif
        @endif
        @if($tooltip)
            data-bs-toggle="tooltip"
            data-bs-title="{{ $tooltip }}"
        @endif
    >{{ $label }}</a>
@endif