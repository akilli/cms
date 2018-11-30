/**
 * Rich Text Editor
 */
'use strict';

(function (document, app, CKEDITOR) {
    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('textarea[data-type=rte]'), function (item) {
            CKEDITOR.replace(item, app.rte);
        });
    });
})(document, app, CKEDITOR);
