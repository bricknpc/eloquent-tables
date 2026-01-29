data-{{ $dataNamespace }}-confirm="true"
data-{{ $dataNamespace }}-confirm-target="#confirm-{{ $id }}"
@if($understandValue)
    data-{{ $dataNamespace }}-confirm-value="{{ $understandValue }}"
    data-{{ $dataNamespace }}-confirm-value-input="confirm-value-{{ $id }}"
@endif
@if($isBulk)
    data-{{ $dataNamespace }}-mass-action-form="true"
@endif