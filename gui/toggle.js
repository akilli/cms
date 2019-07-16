/**
 * Toggle
 */
'use strict';

(document => {
    document.addEventListener('DOMContentLoaded', () => {
        [].forEach.call(document.querySelectorAll('a[data-action=toggle]'), a => {
            a.addEventListener('click', () => {
                const dt = a.getAttribute('data-target');
                const target = dt ? document.getElementById(dt) : null;

                if (a.hasAttribute('data-toggle')) {
                    a.removeAttribute('data-toggle');

                    if (target) {
                        target.setAttribute('data-toggle', '');
                    }
                } else {
                    a.setAttribute('data-toggle', '');

                    if (target) {
                        target.setAttribute('data-toggle', 'open');
                        target.scrollIntoView(true);
                    }
                }
            });
        });
    });
})(document);
