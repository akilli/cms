/**
 * Input password autocomplete fix
 */
'use strict';

(function (document) {
    document.addEventListener('DOMContentLoaded', function () {
        [].forEach.call(document.querySelectorAll('input[type=password]'), function (item) {
            item.setAttribute('readonly', 'readonly');
            item.addEventListener('focus', function () {
                this.removeAttribute('readonly');
            });
        });
    });
})(document);
