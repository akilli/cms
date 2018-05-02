'use strict';

(function (document) {
    document.addEventListener('DOMContentLoaded', function () {
        // Input password autocomplete fix
        Array.prototype.forEach.call(document.querySelectorAll('input[type=password]'), function (item) {
            item.setAttribute('readonly', 'readonly');
            item.addEventListener('focus', function () {
                this.removeAttribute('readonly');
            });
        });
    });
})(document);
