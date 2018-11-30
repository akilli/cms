/**
 * Link Types
 */
'use strict';

(function (document) {
    /**
     * Determines different link types
     */
    function links() {
        Array.prototype.forEach.call(document.querySelectorAll('a[href]:not([role])'), function (item) {
            const href = item.getAttribute('href');
            const ext = href.match(/^https?:\/\//);

            if (ext) {
                item.setAttribute('target', '_blank');
            }

            if (!!item.querySelector('img')) {
                item.setAttribute('data-link', 'img');
            } else if (href.indexOf('/file/') === 0) {
                item.setAttribute('data-link', 'file');
            } else if (href.indexOf('/') === 0) {
                item.setAttribute('data-link', 'intern');
            } else if (href.indexOf('mailto:') === 0) {
                item.setAttribute('data-link', 'email');
            } else if (ext) {
                item.setAttribute('data-link', 'extern');
            }
        });
    }

    /**
     * Event Listener
     */
    document.addEventListener('DOMContentLoaded', links);
})(document);
