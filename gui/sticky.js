/**
 * Minimal position sticky polyfill
 */
'use strict';

(function (document, window) {
    window.addEventListener('load', function () {
        Array.prototype.forEach.call(document.querySelectorAll('[data-sticky]'), function (item) {
            if (window.getComputedStyle(item).getPropertyValue('position') === 'sticky') {
                return;
            }

            setTimeout(function() {
                const pos = item.offsetTop;
                const width = window.getComputedStyle(item.parentElement).getPropertyValue('width');

                window.addEventListener('scroll', function () {
                    if (window.pageYOffset >= pos) {
                        item.setAttribute('data-sticky', 'fixed');
                        item.style.width = width;
                    } else {
                        item.setAttribute('data-sticky', '');
                        item.removeAttribute('style');
                    }
                });
            });
        });
    });
})(document, window);
