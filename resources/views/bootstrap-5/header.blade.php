@php
    use BrickNPC\EloquentTables\Actions\ActionRenderer;
    use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

    /** @var ActionRenderer $actionRenderer */
@endphp

<div class="d-flex align-items-center">
    @if($tableActionCount > 0)
        <div class="d-flex align-items-center table-actions me-3">
            @foreach($tableActions as $tableAction)
                {!! $actionRenderer->render($tableAction, new ActionContext($request, $config)) !!}
            @endforeach
        </div>
    @endif
    @if($showSearchForm)
        <div class="d-flex align-items-center table-search">
            <form action="{{ $tableSearchUrl }}" method="get" id="search-form-{{ $id }}">
                <div class="input-group">
                    <input type="search" name="{{ $searchQueryName }}" class="form-control border-{{ $mainTableStyle }}" placeholder="{{ __('Search') }}" value="{{ $searchQuery }}"/>
                    <button class="btn btn-outline-{{ $mainTableStyle }} d-flex align-items-center" type="submit" form="search-form-{{ $id }}">{{ $searchIcon }}</button>
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
        @if($bulkActionCount > 0)
            <div class="d-flex align-items-center table-mass-actions">
                @foreach($bulkActions as $bulkAction)
                    {!! $actionRenderer->render($bulkAction, new ActionContext($request, $config)->isBulk()) !!}
                @endforeach
            </div>
        @endif
        @if(isset($perPageOptions) && count($perPageOptions) > 0)
            <div class="d-flex align-items-center table-per-page-options ms-3">
                <form action="{{ $fullUrl }}" method="get" id="per-page-form-{{ $id }}">
                    <select name="{{ $perPageName }}" onchange="this.form.submit()" class="form-select border-{{ $mainTableStyle }}">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}" @if ($option === $perPage) selected="selected" @endif>{{ $option }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        @endif
    </div>
</div>