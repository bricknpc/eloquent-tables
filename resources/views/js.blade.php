<script>
    document.addEventListener('DOMContentLoaded', () => {
        const bootstrapLoaded = window.bootstrap !== undefined;

        if (!bootstrapLoaded) {
            console.warn('Eloquent Tables: Bootstrap JS is not loaded. Refer to the installation instructions on how to load the Bootstrap JS. Without it, javascript functions that depend on Bootstrap, like confirmation modals, will not work.');
        }

        document.querySelector('[data-{{ $dataNamespace }}-select-all="true"]').addEventListener('change', event => {
            document.querySelectorAll('[name="selected[]"]').forEach(key => {
                key.checked = event.target.checked;
            });
        });

        document.querySelectorAll('[data-{{ $dataNamespace }}-mass-action-form="true"]').forEach(element => {
            element.addEventListener('submit', event => {
                event.preventDefault();

                if(bootstrapLoaded && element.hasAttribute('data-{{ $dataNamespace }}-confirm')) {
                    const modalElement = document.querySelector(element.getAttribute('data-{{ $dataNamespace }}-confirm-target'));
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();

                    modalElement.querySelector('[data-{{ $dataNamespace }}-confirm-submit="true"]').addEventListener('click', event => {
                        if(element.hasAttribute('data-{{ $dataNamespace }}-confirm-value')) {
                            const confirmValue = element.getAttribute('data-{{ $dataNamespace }}-confirm-value');
                            const confirmValueInput = document.querySelector('#'+element.getAttribute('data-{{ $dataNamespace }}-confirm-value-input'));

                            if (confirmValueInput.value !== confirmValue) {
                                confirmValueInput.classList.add('is-invalid');

                                return;
                            }
                        }

                        document.querySelectorAll('[name="selected[]"]:checked').forEach(selected => {
                            const keys = document.createElement('input');
                            keys.name = 'keys[]';
                            keys.type = 'hidden';
                            keys.value = selected.value;

                            element.appendChild(keys);
                        });

                        element.submit();
                    });
                }
            });
        });

        document.querySelectorAll('[data-{{ $dataNamespace }}-confirm="true"]').forEach(element => {
            if (element.tagName === 'FORM') {
                element.addEventListener('submit', submitEvent => {
                    submitEvent.preventDefault();

                    if(bootstrapLoaded && element.hasAttribute('data-{{ $dataNamespace }}-confirm')) {
                        const modalElement = document.querySelector(element.getAttribute('data-{{ $dataNamespace }}-confirm-target'));
                        const modal = new bootstrap.Modal(modalElement);
                        modal.show();

                        modalElement.querySelector('[data-{{ $dataNamespace }}-confirm-submit="true"]').addEventListener('click', event => {
                            if(element.hasAttribute('data-{{ $dataNamespace }}-confirm-value')) {
                                const confirmValue = element.getAttribute('data-{{ $dataNamespace }}-confirm-value');
                                const confirmValueInput = document.querySelector('#'+element.getAttribute('data-{{ $dataNamespace }}-confirm-value-input'));

                                if (confirmValueInput.value !== confirmValue) {
                                    confirmValueInput.classList.add('is-invalid');

                                    return;
                                }
                            }

                            element.submit();
                        });
                    }
                });
            } else {
                element.addEventListener('click', clickEvent => {
                    clickEvent.preventDefault();

                    if(bootstrapLoaded && element.hasAttribute('data-{{ $dataNamespace }}-confirm')) {
                        const modalElement = document.querySelector(element.getAttribute('data-{{ $dataNamespace }}-confirm-target'));
                        const modal = new bootstrap.Modal(modalElement);
                        modal.show();

                        modalElement.querySelector('[data-{{ $dataNamespace }}-confirm-submit="true"]').addEventListener('click', event => {
                            if(element.hasAttribute('data-{{ $dataNamespace }}-confirm-value')) {
                                const confirmValue = element.getAttribute('data-{{ $dataNamespace }}-confirm-value');
                                const confirmValueInput = document.querySelector('#'+element.getAttribute('data-{{ $dataNamespace }}-confirm-value-input'));

                                if (confirmValueInput.value !== confirmValue) {
                                    confirmValueInput.classList.add('is-invalid');

                                    return;
                                }
                            }

                            document.location.href = element.getAttribute('href');
                        });
                    }
                })
            }
        });
    });
</script>