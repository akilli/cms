/**
 * Privileges
 */
'use strict';

(document => {
    document.addEventListener('DOMContentLoaded', () => {
        const sel = 'input[type=checkbox][multiple]';
        const all = document.querySelector(`:root[data-entity=role][data-action=edit] ${sel}#attr-priv-_all_`);

        if (all) {
            const call = () => {
                all.form.querySelectorAll(`${sel}[name="${all.name}"]:not(#attr-priv-_all_)`).forEach(item => {
                    if (all.checked) {
                        item.checked = false;
                        item.setAttribute('disabled', '');
                    } else {
                        item.removeAttribute('disabled');
                    }
                });
            };
            all.addEventListener('change', call);
            call();
        }
    });
})(document);
