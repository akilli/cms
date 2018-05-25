/**
 * Toggle
 */
'use strict';

(function (document) {
    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('span[data-act=toggle]'), function (item) {
            item.addEventListener('click', function () {
                if (this.hasAttribute('data-toggle')) {
                    this.removeAttribute('data-toggle');
                } else {
                    this.setAttribute('data-toggle', '');
                }
            });
        });
    });
})(document);
