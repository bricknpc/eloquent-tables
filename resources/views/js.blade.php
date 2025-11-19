<script>
    class EloquentTables {
        /**
         * @type {string}
         */
        dataNamespace;
        /**
         * @type {boolean}
         */
        bootstrapLoaded;

        /**
         * @param {string} dataNamespace
         */
        constructor(dataNamespace) {
            this.dataNamespace = dataNamespace;
            this.bootstrapLoaded = window.bootstrap !== undefined;

            if (!this.bootstrapLoaded) {
                console.warn('Eloquent Tables: Bootstrap JS is not loaded. Refer to the installation instructions on how to load the Bootstrap JS. Without it, javascript functions that depend on Bootstrap, like confirmation modals, will not work.');
            }
        }

        init() {
            this.initSelectAll();
            this.initMassActionForms();
            this.initConfirmElements();
        }

        initSelectAll() {
            const selectAllElement = document.querySelector(`[data-${this.dataNamespace}-select-all="true"]`);

            if (!selectAllElement) {
                return;
            }

            selectAllElement.addEventListener('change', event => {
                document.querySelectorAll('[name="selected[]"]').forEach(checkbox => {
                    checkbox.checked = event.target.checked;
                });
            });
        }

        initMassActionForms() {
            const forms = document.querySelectorAll(`[data-${this.dataNamespace}-mass-action-form="true"]`);

            forms.forEach(form => {
                form.addEventListener('submit', event => {
                    event.preventDefault();
                    this.handleFormSubmit(form);
                });
            });
        }

        initConfirmElements() {
            const elements = document.querySelectorAll(`[data-${this.dataNamespace}-confirm="true"]`);

            elements.forEach(element => {
                // Skip mass action forms as they're handled separately
                if (element.hasAttribute(`data-${this.dataNamespace}-mass-action-form`)) {
                    return;
                }

                if (element.tagName === 'FORM') {
                    element.addEventListener('submit', event => {
                        event.preventDefault();
                        this.handleConfirmation(element, () => element.submit());
                    });
                } else {
                    element.addEventListener('click', event => {
                        event.preventDefault();
                        this.handleConfirmation(element, () => {
                            document.location.href = element.getAttribute('href');
                        });
                    });
                }
            });
        }

        /**
         * @param {HTMLFormElement} form
         */
        handleFormSubmit(form) {
            if (!form.hasAttribute(`data-${this.dataNamespace}-confirm`)) {
                return;
            }

            this.handleConfirmation(form, () => {
                document.querySelectorAll('[name="selected[]"]:checked').forEach(selected => {
                    const input = document.createElement('input');
                    input.name = 'keys[]';
                    input.type = 'hidden';
                    input.value = selected.value;
                    form.appendChild(input);
                });
                form.submit();
            });
        }

        /**
         * @param {HTMLFormElement|HTMLLinkElement} element
         * @param {CallableFunction} onConfirm
         */
        handleConfirmation(element, onConfirm) {
            if (!element.hasAttribute(`data-${this.dataNamespace}-confirm`) || !this.bootstrapLoaded) {
                return onConfirm();
            }

            const modalSelector = element.getAttribute(`data-${this.dataNamespace}-confirm-target`);
            const modalElement = document.querySelector(modalSelector);

            if (!modalElement) {
                return;
            }

            const modal = new bootstrap.Modal(modalElement);
            modal.show();

            const confirmButton = modalElement.querySelector(`[data-${this.dataNamespace}-confirm-submit="true"]`);

            if (!confirmButton) {
                return;
            }

            // Use a new event listener to avoid stacking listeners
            const handleConfirmClick = () => {
                if (this.validateConfirmValue(element)) {
                    onConfirm();
                }
            };

            confirmButton.addEventListener('click', handleConfirmClick);
        }

        /**
         * @param {HTMLLinkElement|HTMLFormElement} element
         *
         * @returns {boolean}
         */
        validateConfirmValue(element) {
            if (!element.hasAttribute(`data-${this.dataNamespace}-confirm-value`)) {
                return true;
            }

            const confirmValue = element.getAttribute(`data-${this.dataNamespace}-confirm-value`);
            const inputId = element.getAttribute(`data-${this.dataNamespace}-confirm-value-input`);
            const confirmInput = document.querySelector(`#${inputId}`);

            if (!confirmInput) {
                return false;
            }

            confirmInput.addEventListener('input', () => {
                confirmInput.classList.remove('is-invalid');
            });

            if (confirmInput.value !== confirmValue) {
                confirmInput.classList.add('is-invalid');

                return false;
            }

            return true;
        }
    }

    // Initialise on DOM ready
    document.addEventListener('DOMContentLoaded', () => {
        const tables = new EloquentTables('{{ $dataNamespace }}');
        tables.init();
    });
</script>