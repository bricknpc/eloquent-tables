<div class="alert alert-danger">
    <p class="mb-3"><strong>{{ __('We encountered the following errors:') }}</strong></p>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>