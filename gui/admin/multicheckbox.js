/**
 * Multi-checkbox required fix
 */
'use strict';

(document => {
    document.addEventListener('DOMContentLoaded', () => {
        const sel = 'input[type=checkbox][multiple]';

        document.querySelectorAll(`${sel}[required]`).forEach(item => {
            item.addEventListener('change', () => {
                const req = item.form.querySelector(`${sel}[name="${item.name}"]:checked`);

                item.form.querySelectorAll(`${sel}[name="${item.name}"]`).forEach(sib => {
                    if (req) {
                        sib.removeAttribute('required');
                    } else {
                        sib.setAttribute('required', 'required');
                    }
                });
            });
        });
    });
})(document);
