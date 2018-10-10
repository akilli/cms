/**
 * Delete confirmation
 */
'use strict';

(function (document, app) {
    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('a[data-action=delete]'), function (item) {
            item.addEventListener('click', function (event) {
                if (!confirm(app.i18n('Please confirm delete operation'))) {
                    event.preventDefault();
                }
            });
        });
    });
})(document, app);
