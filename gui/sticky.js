/**
 * Sticky navigation polyfill
 */
'use strict';

(function (document, window) {
    window.addEventListener('load', function () {
        const nav = document.querySelector('#menu[data-sticky]');

        if (!!nav && window.getComputedStyle(nav).getPropertyValue('position') !== 'sticky') {
            setTimeout(function() {
                const pos = nav.offsetTop;
                const width = window.getComputedStyle(nav.parentElement).getPropertyValue('width');

                window.addEventListener('scroll', function () {
                    if (window.pageYOffset >= pos) {
                        nav.setAttribute('data-sticky', 'fixed');
                        nav.style.width = width;
                    } else {
                        nav.setAttribute('data-sticky', '');
                        nav.removeAttribute('style');
                    }
                });
            });
        }
    });
})(document, window);
