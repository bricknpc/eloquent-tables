<div class="d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center table-actions">
        @if($tableActionCount > 1)
            <div class="btn-group">
        @endif
        @foreach($tableActions as $tableAction)
            {!! $tableActionViewBuilder->build($tableAction) !!}
        @endforeach
        @if($tableActionCount > 1)
            </div>
        @endif
    </div>
</div>