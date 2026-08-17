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

            warnAboutDuplicateNames(tables);

            tables.forEach(table => {
                storePreferences(table);
                new EloquentTables(table, '{{ $dataNamespace }}').init();
            });
        });

        // Well under the 4KB a browser allows one cookie, leaving room for the rest of the value.
        const PREFERENCE_BYTE_BUDGET = 3500;

        /**
         * Persist what the current URL says about this table.
         *
         * The URL is the source of truth: opening any link that carries a table's per-page value or
         * sort makes that the visitor's stored choice, whether they clicked a control or followed a
         * link someone sent them.
         *
         * @param {HTMLElement} table
         */
        function storePreferences(table) {
            const cookieName = table.getAttribute('data-{{ $dataNamespace }}-preferences-cookie');
            const name = table.getAttribute('data-{{ $dataNamespace }}-table-name');

            // Absent when preferences are switched off in config, in which case nothing is stored.
            if (!cookieName || !name) {
                return;
            }

            const perPageKey = table.getAttribute('data-{{ $dataNamespace }}-preferences-per-page-key');
            const sortKey = table.getAttribute('data-{{ $dataNamespace }}-preferences-sort-key');
            const params = new URLSearchParams(window.location.search);

            const preference = {};
            let touched = false;

            if (perPageKey && params.has(perPageKey)) {
                preference.per_page = params.get(perPageKey);
                touched = true;
            }

            if (sortKey) {
                const sort = {};
                let sorted = false;

                params.forEach((value, key) => {
                    // Matches sortKey[column]; an exact sortKey means the sort was cleared.
                    if (key.startsWith(sortKey + '[') && key.endsWith(']')) {
                        sort[key.slice(sortKey.length + 1, -1)] = value;
                        sorted = true;
                    }
                });

                if (sorted || params.has(sortKey)) {
                    preference.sort = sort;
                    touched = true;
                }
            }

            if (!touched) {
                return;
            }

            const all = readPreferences(cookieName);

            // Merge rather than replace: the URL may speak to only one of the two.
            all[name] = Object.assign({}, all[name], preference);

            writePreferences(cookieName, all);
        }

        /**
         * @param {string} cookieName
         *
         * @returns {Object}
         */
        function readPreferences(cookieName) {
            const match = document.cookie
                .split('; ')
                .find(row => row.startsWith(cookieName + '='));

            if (!match) {
                return {};
            }

            try {
                const parsed = JSON.parse(decodeURIComponent(match.slice(cookieName.length + 1)));

                return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
            } catch (error) {
                // A hand-edited or truncated cookie is replaced rather than allowed to break the page.
                return {};
            }
        }

        /**
         * @param {string} cookieName
         * @param {Object} all
         */
        function writePreferences(cookieName, all) {
            let value = encodeURIComponent(JSON.stringify(all));

            // A browser silently truncates an oversized cookie, which would corrupt every table's
            // preferences at once. Drop the oldest entries instead until the value fits.
            const names = Object.keys(all);

            while (value.length > PREFERENCE_BYTE_BUDGET && names.length > 1) {
                delete all[names.shift()];
                value = encodeURIComponent(JSON.stringify(all));
            }

            const expires = new Date();
            expires.setFullYear(expires.getFullYear() + 1);

            document.cookie = cookieName + '=' + value
                + '; path=/; expires=' + expires.toUTCString()
                + '; SameSite=Lax';
        }

        /**
         * Two tables sharing a name share one query namespace and one stored preference, so sorting
         * one sorts both. That is legitimate when deliberate, and a mistake the rest of the time, so
         * it is reported rather than prevented.
         *
         * @param {NodeListOf<HTMLElement>} tables
         */
        function warnAboutDuplicateNames(tables) {
            const counts = new Map();

            tables.forEach(table => {
                const name = table.getAttribute('data-{{ $dataNamespace }}-table-name');

                if (name) {
                    counts.set(name, (counts.get(name) || 0) + 1);
                }
            });

            counts.forEach((count, name) => {
                if (count > 1) {
                    console.warn(
                        'Eloquent Tables: ' + count + ' tables on this page are named "' + name + '", so they ' +
                        'share their query parameters and their stored preferences. Override name() on one of ' +
                        'them to keep them independent.'
                    );
                }
            });
        }
    })();
</script>
