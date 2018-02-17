'use strict';

(function (window, document, app) {
    document.addEventListener('DOMContentLoaded', function () {
        // Input password autocomplete fix
        document.querySelectorAll('input[type=password]').forEach(function (item) {
            item.setAttribute('readonly', true);
            item.addEventListener('focus', function () {
                this.removeAttribute('readonly');
            });
        });

        // Delete buttons and links
        document.querySelectorAll('[data-act=delete]').forEach(function (item) {
            item.addEventListener('click', function (event) {
                if (!confirm(app.i18n('Please confirm delete operation'))) {
                    event.preventDefault();
                }
            });
        });

        // RTE browser
        document.querySelectorAll('span.rte').forEach(function (item) {
            item.addEventListener('click', function () {
                window.opener.CKEDITOR.tools.callFunction(this.getAttribute('data-rte'), this.getAttribute('data-url'));
                window.close();
            });
        });
    });
})(window, document, app);
