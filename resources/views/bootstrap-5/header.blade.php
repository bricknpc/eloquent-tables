<div class="d-flex align-items-center">
    <div class="d-flex align-items-center table-actions me-3">
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
    @if($showSearchForm)
        <div class="d-flex align-items-center table-search">
            <form action="{{ $tableSearchUrl }}" method="get" id="search-form-{{ $id }}">
                <div class="input-group">
                    <input type="search" name="{{ $searchQueryName }}" class="form-control border-primary" placeholder="{{ __('Search') }}" value="{{ $searchQuery }}" />
                    <button class="btn btn-outline-primary d-flex align-items-center" type="submit" form="search-form-{{ $id }}">{{ $searchIcon }}</button>
                </div>
            </form>
        </div>
    @endif
    @if($massActionCount > 0)
        <div class="d-flex align-items-center table-mass-actions ms-auto">
            @foreach($massActions as $massAction)
                {!! $massActionViewBuilder->build($massAction, $request) !!}
            @endforeach
        </div>
    @endif
</div>