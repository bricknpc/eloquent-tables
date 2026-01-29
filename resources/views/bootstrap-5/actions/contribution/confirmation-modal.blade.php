<div class="modal" id="confirm-{{ $id }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p>{{ $text }}</p>
                @if($understandValue)
                    <p>
                        {{ new \Illuminate\Support\HtmlString(__('To confirm this action, please type the text <code>:confirmValue</code> into the textbox below.', ['confirmValue' => $understandValue])) }}
                    </p>
                    <p>
                        <input type="text" name="confirm-value" id="confirm-value-{{ $id }}" class="form-control" />
                    </p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" data-{{ $dataNamespace }}-cancel-confirm="true">{{ $cancelText ?? __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" data-{{ $dataNamespace }}-confirm-submit="true">{{ $confirmationText ?? __('Yes, confirm') }}</button>
            </div>
        </div>
    </div>
</div>