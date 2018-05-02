'use strict';

(function (document, app) {
    document.addEventListener('DOMContentLoaded', function () {
        // Delete buttons and links
        Array.prototype.forEach.call(document.querySelectorAll('a[data-act=delete]'), function (item) {
            item.addEventListener('click', function (event) {
                if (!confirm(app.i18n('Please confirm delete operation'))) {
                    event.preventDefault();
                }
            });
        });
    });
})(document, app);
