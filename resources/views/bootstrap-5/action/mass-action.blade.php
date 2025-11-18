<form
    method="{{ $method->getFormMethod() }}"
    action="{{ $action }}"
    name="mass-action-{{ $id }}"
    id="mass-action-{{ $id }}"
    data-{{ $dataNamespace }}-mass-action-form="true"
    @if($confirm)
        data-{{ $dataNamespace }}-confirm="true"
        data-{{ $dataNamespace }}-confirm-target="#mass-delete-confirm-{{ $id }}"
        @if($confirmValue)
            data-{{ $dataNamespace }}-confirm-value="{{ $confirmValue }}"
        data-{{ $dataNamespace }}-confirm-value-input="confirm-value-{{ $id }}"
        @endif
    @endif
>
    @csrf
    @method($method->value)
    <button
        type="submit"
        class="btn {{ $styles }}"
        form="mass-action-{{ $id }}"
        @if($tooltip)
            data-bs-toggle="tooltip"
            data-bs-title="{{ $tooltip }}"
        @endif
    >{{ $label }}</button>
    @if($confirm)
        <div class="modal" id="mass-delete-confirm-{{ $id }}">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <p>{{ $confirm }}</p>
                        @if($confirmValue)
                            <p>
                                {{ new \Illuminate\Support\HtmlString(__('To confirm this action, please type the text <code>:confirmValue</code> into the textbox below.', ['confirmValue' => $confirmValue])) }}
                            </p>
                            <p>
                                <input type="text" name="confirm-value" id="confirm-value-{{ $id }}" class="form-control" />
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
</form>