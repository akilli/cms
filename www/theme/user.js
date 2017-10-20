'use strict';

(function (document, app) {
    document.addEventListener('DOMContentLoaded', () => {
        // Input password autocomplete fix
        const pwd = document.querySelectorAll('input[type=password]');

        for (let i = 0; i < pwd.length; i++) {
            pwd[i].setAttribute('readonly', true);
            pwd[i].addEventListener('focus', function () {
                this.removeAttribute('readonly');
            })
        }

        // Delete buttons and links
        const del = document.querySelectorAll('.delete');

        for (let i = 0; i < del.length; i++) {
            del[i].addEventListener('click', event => {
                if (!confirm(app.i18n('Please confirm delete operation'))) {
                    event.preventDefault();
                }
            })
        }
    });
})(document, app);
