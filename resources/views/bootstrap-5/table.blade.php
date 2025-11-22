@if(isset($errors) && count($errors) > 0)
    <section class="mb-3">
        @include('eloquent-tables::bootstrap-5.errors')
    </section>
@endif
<section>
    <div class="card mb-3">
        <div class="card-body">
            @include('eloquent-tables::bootstrap-5.header')
        </div>
    </div>

    <div class="card mb-3 table-responsive">
        <table class="table {{ $tableStyles }}">
            @include('eloquent-tables::bootstrap-5.table.thead')
            @include('eloquent-tables::bootstrap-5.table.tbody')
            @include('eloquent-tables::bootstrap-5.table.tfoot')
        </table>
    </div>

    @if($links)
        <div class="card mb-3">
            <div class="card-body pb-0">
                @include('eloquent-tables::bootstrap-5.links')
            </div>
        </div>
    @endif
</section>