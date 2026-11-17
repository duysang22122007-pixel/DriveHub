(() => {
    'use strict';

    const prefix = 'drivehub:form-draft:v1:';
    const scope = String(window.DriveHubFormDraftScope || 'guest');
    const lastSubmittedKey = `${prefix}${encodeURIComponent(scope)}:last-submitted`;

    const storage = () => window.sessionStorage;
    const isSensitive = (name) => /(?:password|passwd|secret|csrf|token|credential)/i.test(name);

    const formKey = (form) => {
        const action = form.querySelector('input[name="action"][type="hidden"]')?.value || '';
        const closedDate = form.querySelector('input[name="closed_date"][type="hidden"]')?.value || '';
        const identity = form.dataset.draftKey || [window.location.pathname, form.action, action, closedDate].join('|');

        return `${prefix}${encodeURIComponent(scope)}:${encodeURIComponent(identity)}`;
    };

    const readDraft = (form) => {
        const key = formKey(form);
        try {
            const serialized = storage().getItem(key);
            if (serialized === null) {
                return { key, fields: {}, meta: {} };
            }

            const draft = JSON.parse(serialized);
            if (!draft || typeof draft !== 'object' || Array.isArray(draft)) {
                throw new TypeError('Unexpected form draft structure.');
            }

            return {
                key,
                fields: draft.fields && typeof draft.fields === 'object' ? draft.fields : {},
                meta: draft.meta && typeof draft.meta === 'object' ? draft.meta : {},
            };
        } catch (error) {
            console.error('Unable to read a saved form draft.', error);
            try {
                storage().removeItem(key);
            } catch (storageError) {
                console.error('Unable to remove an invalid form draft.', storageError);
            }

            return { key, fields: {}, meta: {} };
        }
    };

    const eligibleControls = (form) => Array.from(form.elements).filter((control) => {
        if (!control.name || isSensitive(control.name)) {
            return false;
        }

        return !['password', 'file', 'submit', 'button', 'reset', 'image'].includes(control.type);
    });

    const capture = (form) => {
        const fields = {};
        const controls = eligibleControls(form);
        const names = Array.from(new Set(controls.map((control) => control.name)));

        names.forEach((name) => {
            const group = controls.filter((control) => control.name === name);
            const checkboxes = group.filter((control) => control.type === 'checkbox');
            const radios = group.filter((control) => control.type === 'radio');

            if (checkboxes.length > 0) {
                fields[name] = checkboxes.filter((control) => control.checked).map((control) => control.value);
            } else if (radios.length > 0) {
                const selected = radios.find((control) => control.checked);
                if (selected) {
                    fields[name] = selected.value;
                }
            } else {
                const control = group[0];
                fields[name] = control instanceof HTMLSelectElement && control.multiple
                    ? Array.from(control.selectedOptions).map((option) => option.value)
                    : control.value;
            }
        });

        return fields;
    };

    const save = (form) => {
        const draft = readDraft(form);
        draft.fields = capture(form);
        try {
            storage().setItem(draft.key, JSON.stringify(draft));
        } catch (error) {
            console.error('Unable to save a form draft in this browser tab.', error);
        }
    };

    const setMeta = (form, name, value) => {
        const draft = readDraft(form);
        draft.meta[name] = value;
        try {
            storage().setItem(draft.key, JSON.stringify(draft));
        } catch (error) {
            console.error('Unable to save form navigation state in this browser tab.', error);
        }
    };

    const value = (form, name, fallback = '') => {
        const draft = readDraft(form);
        return Object.prototype.hasOwnProperty.call(draft.fields, name) ? draft.fields[name] : fallback;
    };

    const meta = (form, name, fallback = null) => {
        const draft = readDraft(form);
        return Object.prototype.hasOwnProperty.call(draft.meta, name) ? draft.meta[name] : fallback;
    };

    const restore = (form) => {
        const draft = readDraft(form);
        const controls = eligibleControls(form);
        const names = Array.from(new Set(controls.map((control) => control.name)));

        names.forEach((name) => {
            if (!Object.prototype.hasOwnProperty.call(draft.fields, name)) {
                return;
            }

            const group = controls.filter((control) => control.name === name);
            const checkboxes = group.filter((control) => control.type === 'checkbox');
            const radios = group.filter((control) => control.type === 'radio');
            const storedValue = draft.fields[name];

            if (checkboxes.length > 0) {
                const selectedValues = Array.isArray(storedValue) ? storedValue.map(String) : [];
                checkboxes.forEach((control) => {
                    control.checked = selectedValues.includes(control.value);
                });
            } else if (radios.length > 0) {
                radios.forEach((control) => {
                    control.checked = control.value === String(storedValue);
                });
            } else {
                const control = group[0];
                if (control instanceof HTMLSelectElement) {
                    if (control.multiple && Array.isArray(storedValue)) {
                        Array.from(control.options).forEach((option) => {
                            option.selected = storedValue.map(String).includes(option.value);
                        });
                    } else if (control.options.length > 0
                        && Array.from(control.options).some((option) => option.value === String(storedValue))) {
                        control.value = String(storedValue);
                    } else if (control.tagName === 'SELECT') {
                        if (!form.__driveHubPendingDraftValues) {
                            form.__driveHubPendingDraftValues = {};
                        }
                        form.__driveHubPendingDraftValues[name] = storedValue;
                    } else {
                        control.value = String(storedValue);
                    }
                } else {
                    control.value = String(storedValue);
                }
            }

            group.forEach((control) => {
                if (control.type === 'hidden') {
                    return;
                }
                control.dispatchEvent(new Event('input', { bubbles: true }));
                control.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        form.dispatchEvent(new CustomEvent('drivehub:form-draft-restored', {
            detail: { fields: draft.fields, meta: draft.meta },
        }));
    };

    if (window.DriveHubClearSubmittedDraft === true) {
        try {
            const submittedKey = storage().getItem(lastSubmittedKey);
            if (submittedKey && submittedKey.startsWith(prefix)) {
                storage().removeItem(submittedKey);
            }
            storage().removeItem(lastSubmittedKey);
        } catch (error) {
            console.error('Unable to clear the form draft after a successful save.', error);
        }
    }

    window.DriveHubFormDrafts = {
        value,
        meta,
        setMeta,
        clear(form) {
            const key = formKey(form);
            try {
                storage().removeItem(key);
            } catch (error) {
                console.error('Unable to clear this form draft.', error);
            }
        },
    };

    document.addEventListener('DOMContentLoaded', () => {
        const forms = Array.from(document.forms).filter((form) => {
            const method = (form.method || 'get').toLowerCase();
            const action = new URL(form.action, window.location.href);

            return method === 'post'
                && !form.hasAttribute('data-no-draft')
                && !/\/logout\/?$/i.test(action.pathname);
        });

        forms.forEach((form) => {
            restore(form);
            form.addEventListener('input', () => save(form));
            form.addEventListener('change', () => save(form));
            form.addEventListener('submit', () => {
                save(form);
                try {
                    storage().setItem(lastSubmittedKey, formKey(form));
                } catch (error) {
                    console.error('Unable to track the submitted form draft.', error);
                }
            });
        });
    });
})();
