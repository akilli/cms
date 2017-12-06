'use strict';

(function (window, document, app) {
    document.addEventListener('DOMContentLoaded', function () {
        let item;

        // Input password autocomplete fix
        item = document.querySelectorAll('input[type=password]');

        for (let i = 0; i < item.length; i++) {
            item[i].setAttribute('readonly', true);
            item[i].addEventListener('focus', function () {
                this.removeAttribute('readonly');
            })
        }

        // Delete buttons and links
        item = document.querySelectorAll('.delete');

        for (let i = 0; i < del.length; i++) {
            item[i].addEventListener('click', function (event) {
                if (!confirm(app.i18n('Please confirm delete operation'))) {
                    event.preventDefault();
                }
            })
        }

        // RTE browser
        item = document.querySelectorAll('span.rte');

        for (let i = 0; i < item.length; i++) {
            item[i].addEventListener('click', function () {
                window.opener.CKEDITOR.tools.callFunction(this.getAttribute('data-rte'), this.getAttribute('data-url'));
                window.close();
            })
        }
    });
})(window, document, app);
