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
                    <input type="search" name="{{ $searchQueryName }}" class="form-control" placeholder="{{ __('Search') }}" value="{{ $searchQuery }}" />
                    <button class="btn btn-outline-primary" type="submit" form="search-form-{{ $id }}"><x-bi-search class="d-flex align-items-center" /></button>
                </div>
            </form>
        </div>
    @endif
</div>