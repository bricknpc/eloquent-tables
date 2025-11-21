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
    @if($filterCount > 0)
        <div class="d-flex align-items-center table-filters ms-3">
            @foreach($filters as $filter)
                {!! $filterViewBuilder->build($filter, $request) !!}
            @endforeach
        </div>
    @endif
    <div class="d-flex align-items-center table-header-end ms-auto">
        @if($massActionCount > 0)
            <div class="d-flex align-items-center table-mass-actions">
                @foreach($massActions as $massAction)
                    {!! $massActionViewBuilder->build($massAction, $request) !!}
                @endforeach
            </div>
        @endif
        @if(isset($perPageOptions) && count($perPageOptions) > 0)
            <div class="d-flex align-items-center table-per-page-options ms-3">
                <form action="{{ $fullUrl }}" method="get" id="per-page-form-{{ $id }}">
                    <select name="{{ $perPageName }}" onchange="this.form.submit()" class="form-select border-primary">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}" @if ($option === $perPage) selected="selected" @endif>{{ $option }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        @endif
    </div>
</div>