/**
 * Track validity of input elements in corresponding wrapper div element
 */
'use strict';

(document => {
    /**
     * Check field
     */
    function check(div) {
        if (div.querySelector(':invalid')) {
            div.setAttribute('data-invalid', '');
        } else {
            div.removeAttribute('data-invalid');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form').forEach(form => {
            form.querySelectorAll('div[data-attr] input, div[data-attr] select, div[data-attr] textarea').forEach(input => {
                const call = ev => check(ev.target.closest('div[data-attr]'));
                input.addEventListener('change', call);
                input.addEventListener('input', call);
            });
            form.querySelector('input[type=submit').addEventListener('click', () => form.querySelectorAll('div[data-attr]').forEach(check));
        });
    });
})(document);
