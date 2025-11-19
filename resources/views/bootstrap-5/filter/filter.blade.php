<form action="{{ $action }}" method="get">
    <select name="{{ $queryName }}[{{ $name }}]" class="form-select" aria-label="{{ __('Filter on :name', ['name' => $name]) }}" onchange="this.form.submit()">
        <option value=""></option>
        @foreach($options as $optionKey => $optionValue)
            <option value="{{ $optionKey }}" @if($optionKey === $value) selected="selected" @endif>{{ $optionValue }}</option>
        @endforeach
    </select>
</form>