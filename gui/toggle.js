/**
 * Toggle
 */
'use strict';

(function (document) {
    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('a[data-action=toggle]'), function (item) {
            item.addEventListener('click', function () {
                const dt = this.getAttribute('data-target');
                const target = dt ? document.getElementById(dt) : null;

                if (this.hasAttribute('data-toggle')) {
                    this.removeAttribute('data-toggle');

                    if (!!target) {
                        target.setAttribute('data-toggle', '');
                    }
                } else {
                    this.setAttribute('data-toggle', '');

                    if (!!target) {
                        target.setAttribute('data-toggle', 'open');
                        target.scrollIntoView(true);
                    }
                }
            });
        });
    });
})(document);
