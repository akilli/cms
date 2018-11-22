/**
 * Print Listeners
 */
'use strict';

(function (window, document) {
    /**
     * Prepares print version
     */
    function printBefore() {
        Array.prototype.forEach.call(document.getElementsByTagName('details'), function (item) {
            if (item.hasAttribute('open')) {
                item.setAttribute('data-open', '');
            } else {
                item.setAttribute('open', '');
            }
        });

        Array.prototype.forEach.call(document.querySelectorAll('a[href^="/"]'), function (item) {
            item.setAttribute('data-href', item.getAttribute('href'));
            item.setAttribute('href', item.href);
        });
    }

    /**
     * Restores screen version
     */
    function printAfter() {
        Array.prototype.forEach.call(document.getElementsByTagName('details'), function (item) {
            if (item.hasAttribute('data-open')) {
                item.removeAttribute('data-open');
            } else {
                item.removeAttribute('open');
            }
        });

        Array.prototype.forEach.call(document.querySelectorAll('a[data-href]'), function (item) {
            item.setAttribute('href', item.getAttribute('data-href'));
            item.removeAttribute('data-href');
        });
    }

    /**
     * Event Listener
     */
    window.matchMedia('print').addListener(function (media) {
        if (media.matches) {
            printBefore();
        } else {
            printAfter();
        }
    });
    window.addEventListener('beforeprint', printBefore);
    window.addEventListener('afterprint', printAfter);
})(window, document);
