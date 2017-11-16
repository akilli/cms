'use strict';

(function (window, document, app) {
    document.addEventListener('DOMContentLoaded', function () {
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
            del[i].addEventListener('click', function (event) {
                if (!confirm(app.i18n('Please confirm delete operation'))) {
                    event.preventDefault();
                }
            })
        }

        // RTE browser
        const rte = document.querySelectorAll('span.rte');

        for (let i = 0; i < rte.length; i++) {
            rte[i].addEventListener('click', function () {
                window.opener.CKEDITOR.tools.callFunction(rte[i].getAttribute('data-rte'), rte[i].getAttribute('data-url'));
                window.close();
            })
        }
    });
})(window, document, app);
