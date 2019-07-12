/**
 * Link Types
 */
'use strict';

(function (document) {
    document.addEventListener('DOMContentLoaded', function () {
        [].forEach.call(document.querySelectorAll('a[href]:not([role])'), function (item) {
            const href = item.getAttribute('href');
            const ext = href.match(/^https?:\/\//);

            if (ext) {
                item.setAttribute('target', '_blank');
            }

            if (!!item.querySelector('audio, iframe, img, figure, video')) {
                item.setAttribute('data-link', 'media');
                return;
            }

            if (!item.innerText.trim()) {
                item.parentElement.removeChild(item);
                return;
            }

            if (href.indexOf('/file/') === 0) {
                item.setAttribute('data-link', 'file');
            } else if (href.indexOf('/') === 0) {
                item.setAttribute('data-link', 'intern');
            } else if (href.indexOf('mailto:') === 0) {
                item.setAttribute('data-link', 'email');
            } else if (ext) {
                item.setAttribute('data-link', 'extern');
            }
        });
    });
})(document);
