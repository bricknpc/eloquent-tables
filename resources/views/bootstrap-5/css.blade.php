<style>
    .page-link:hover,
    .page-link:active {
        background-color: var({{ '--bs-' . $activeStyle }}) !important;
        color: var({{ '--bs-' . $mainTableStyle }}) !important;
    }

    /* Fix the last actual button */
    .btn-group > .btn:last-of-type:not(:first-child):not(:last-child) {
        border-top-right-radius: var(--bs-border-radius) !important;
        border-bottom-right-radius: var(--bs-border-radius) !important;
    }
</style>
