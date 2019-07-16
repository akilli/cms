/**
 * Allow autocomplete for login
 */
'use strict';

(document => {
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelector('html[data-url="/account/login"] input[type=password]').removeAttribute('autocomplete');
    });
})(document);
