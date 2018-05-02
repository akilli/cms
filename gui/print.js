'use strict';

(function (document, window) {
    function printBefore() {
        // Details
        Array.prototype.forEach.call(document.getElementsByTagName('details'), function (item) {
            if (item.hasAttribute('open')) {
                item.setAttribute('data-open', '');
            } else {
                item.setAttribute('open', '');
            }
        });

        // Links
        Array.prototype.forEach.call(document.querySelectorAll('a[href^="/"]'), function (item) {
            item.setAttribute('data-href', item.getAttribute('href'));
            item.setAttribute('href', item.href);
        });
    }

    function printAfter() {
        // Details
        Array.prototype.forEach.call(document.getElementsByTagName('details'), function (item) {
            if (item.hasAttribute('data-open')) {
                item.removeAttribute('data-open');
            } else {
                item.removeAttribute('open');
            }
        });

        // Links
        Array.prototype.forEach.call(document.querySelectorAll('a[data-href]'), function (item) {
            item.setAttribute('href', item.getAttribute('data-href'));
            item.removeAttribute('data-href');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Print version
        const mql = window.matchMedia('print');
        mql.addListener(function (media) {
            if (media.matches) {
                printBefore();
            } else {
                printAfter();
            }
        });

        window.addEventListener('beforeprint', printBefore);
        window.addEventListener('afterprint', printAfter);
    });
})(document, window);
