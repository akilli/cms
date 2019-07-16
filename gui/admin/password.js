/**
 * Input password autocomplete fix
 */
'use strict';

(document => {
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('input[type=password]').forEach(item => {
            item.setAttribute('readonly', 'readonly');
            item.addEventListener('focus', () => item.removeAttribute('readonly'));
        });
    });
})(document);
