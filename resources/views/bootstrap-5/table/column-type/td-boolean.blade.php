<div class="text-center d-flex justify-content-center">
    @if($value)
        <span class="text-success">{{ $checkIcon }}</span>
    @else
        <span class="text-danger">{{ $crossIcon }}</span>
    @endif
</div>