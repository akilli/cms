/**
 * Link Types
 */
'use strict';

(document => {
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('a[href]:not([role])').forEach(a => {
            const href = a.getAttribute('href');
            const ext = href.match(/^https?:\/\//);

            if (ext) {
                a.setAttribute('target', '_blank');
            }

            if (a.querySelector('audio, iframe, img, figure, video')) {
                a.setAttribute('data-link', 'media');
                return;
            }

            if (!a.innerText.trim()) {
                a.parentElement.removeChild(a);
                return;
            }

            if (href.indexOf('/file/') === 0) {
                a.setAttribute('data-link', 'file');
            } else if (href.indexOf('/') === 0) {
                a.setAttribute('data-link', 'intern');
            } else if (href.indexOf('mailto:') === 0) {
                a.setAttribute('data-link', 'email');
            } else if (ext) {
                a.setAttribute('data-link', 'extern');
            }
        });
    });
})(document);
