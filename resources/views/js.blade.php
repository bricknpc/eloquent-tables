<script>
    (() => {
        // A page can contain more than one table, and every table renders this script. The class is declared once and
        // is instantiated for every table on the page.
        if (window.EloquentTables !== undefined) {
            return;
        }

        class EloquentTables {
            /**
             * @type {HTMLElement}
             */
            root;
            /**
             * @type {string}
             */
            dataNamespace;
            /**
             * @type {boolean}
             */
            bootstrapLoaded;

            /**
             * @param {HTMLElement} root
             * @param {string} dataNamespace
             */
            constructor(root, dataNamespace) {
                this.root = root;
                this.dataNamespace = dataNamespace;
                this.bootstrapLoaded = window.bootstrap !== undefined;
            }

            init() {
                this.initSelectAll();
                this.initBulkActionForms();
                this.initConfirmElements();
                this.initHttpModals();
            }

            initSelectAll() {
                const selectAllElement = this.root.querySelector(`[data-${this.dataNamespace}-select-all="true"]`);

                if (!selectAllElement) {
                    return;
                }

                selectAllElement.addEventListener('change', event => {
                    this.root.querySelectorAll('[name="selected[]"]').forEach(checkbox => {
                        checkbox.checked = event.target.checked;
                    });
                });
            }

            initBulkActionForms() {
                const buttons = this.root.querySelectorAll(`[data-${this.dataNamespace}-bulk-action-form="true"]`);

                buttons.forEach(button => {
                    // The id of the form is a random hash, which is not always a valid css selector, so it is looked
                    // up by id instead of with a query selector.
                    let form = document.getElementById(button.getAttribute('form'));
                    button.addEventListener('click', event => {
                        event.preventDefault();
                        this.handleFormSubmit(form, button);
                    });
                });
            }

            initConfirmElements() {
                const elements = this.root.querySelectorAll(`[data-${this.dataNamespace}-confirm="true"]`);

                elements.forEach(element => {
                    // Skip bulk action forms as they're handled separately
                    if (element.hasAttribute(`data-${this.dataNamespace}-bulk-action-form`)) {
                        return;
                    }

                    if (element.tagName === 'BUTTON') {
                        element.addEventListener('click', event => {
                            event.preventDefault();
                            let formElement = document.getElementById(element.getAttribute('form'));
                            this.handleConfirmation(element, () => formElement.submit());
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

            initHttpModals() {
                const modals = this.root.querySelectorAll(`[data-${this.dataNamespace}-modal-frame="true"]`);

                modals.forEach(modal => {
                    const frame = modal.querySelector(`[data-${this.dataNamespace}-modal-src]`);

                    if (!frame) {
                        return;
                    }

                    const loading = modal.querySelector(`[data-${this.dataNamespace}-modal-loading="true"]`);
                    const error = modal.querySelector(`[data-${this.dataNamespace}-modal-error="true"]`);

                    frame.addEventListener('load', () => {
                        // The frame also loads when it has no source yet, which happens when the modal is closed.
                        if (!frame.getAttribute('src')) {
                            return;
                        }

                        frame.hidden = false;

                        if (loading) {
                            loading.hidden = true;
                        }
                    });

                    frame.addEventListener('error', () => {
                        frame.hidden = true;

                        if (loading) {
                            loading.hidden = true;
                        }

                        if (error) {
                            error.hidden = false;
                        }
                    });

                    // The source is only set when the modal is opened, so nothing is loaded until it is needed.
                    modal.addEventListener('show.bs.modal', () => {
                        frame.hidden = true;

                        if (loading) {
                            loading.hidden = false;
                        }

                        if (error) {
                            error.hidden = true;
                        }

                        frame.setAttribute('src', frame.getAttribute(`data-${this.dataNamespace}-modal-src`));
                    });

                    // Removing the source stops whatever the frame is doing and loads it again on the next open.
                    modal.addEventListener('hidden.bs.modal', () => {
                        frame.removeAttribute('src');
                    });
                });
            }

            /**
             * @param {HTMLFormElement} form
             * @param {HTMLButtonElement} button
             */
            handleFormSubmit(form, button) {
                this.handleConfirmation(button, () => {
                    this.root.querySelectorAll('[name="selected[]"]:checked').forEach(selected => {
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
                const modalElement = this.root.querySelector(modalSelector);

                if (!modalElement) {
                    return;
                }

                const modal = new bootstrap.Modal(modalElement);
                modal.show();

                const confirmButton = modalElement.querySelector(`[data-${this.dataNamespace}-confirm-submit="true"]`);

                if (!confirmButton) {
                    return;
                }

                // Remove any existing listeners by cloning the button
                const newConfirmButton = confirmButton.cloneNode(true);
                confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);

                // Add the new listener to the cloned button
                newConfirmButton.addEventListener('click', () => {
                    if (this.validateConfirmValue(element)) {
                        onConfirm();
                    }
                }, { once: true }); // Use once: true to automatically remove after one click
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
                const confirmInput = document.getElementById(inputId);

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

        window.EloquentTables = EloquentTables;

        // Initialise every table on the page on DOM ready
        document.addEventListener('DOMContentLoaded', () => {
            const tables = document.querySelectorAll('[data-{{ $dataNamespace }}-table]');

            if (tables.length > 0 && window.bootstrap === undefined) {
                console.warn('Eloquent Tables: Bootstrap JS is not loaded. Refer to the installation instructions on how to load the Bootstrap JS. Without it, javascript functions that depend on Bootstrap, like confirmation modals, will not work.');
            }

            tables.forEach(table => {
                new EloquentTables(table, '{{ $dataNamespace }}').init();
            });
        });
    })();
</script>
