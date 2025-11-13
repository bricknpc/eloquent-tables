<section>
    <div class="card mb-3">
        <div class="card-body">
            @include('eloquent-tables::bootstrap-5.table.header')
        </div>
    </div>

    <div class="card mb-3 table-responsive">
        <table class="table {{ $tableStyles }}">
            @include('eloquent-tables::bootstrap-5.table.thead')
            @include('eloquent-tables::bootstrap-5.table.tbody')
            @include('eloquent-tables::bootstrap-5.table.tfoot')
        </table>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            @include('eloquent-tables::bootstrap-5.table.links')
        </div>
    </div>
</section>