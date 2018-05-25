/**
 * Menu
 */
'use strict';

(function (document, window) {
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle
        const toggle = document.querySelector('#menu[data-toggle] > span[data-act=toggle]');

        if (!!toggle) {
            toggle.addEventListener('click', function () {
                if (this.hasAttribute('data-toggle')) {
                    this.removeAttribute('data-toggle');
                } else {
                    this.setAttribute('data-toggle', '');
                }
            });
        }
    });

    window.addEventListener('load', function () {
        // Minimal sticky menu polyfill
        const sticky = document.querySelector('#menu[data-sticky]');

        if (!!sticky && window.getComputedStyle(sticky).getPropertyValue('position') !== 'sticky') {
            setTimeout(function() {
                const pos = sticky.offsetTop;
                const width = window.getComputedStyle(sticky.parentElement).getPropertyValue('width');

                window.addEventListener('scroll', function () {
                    if (window.pageYOffset >= pos) {
                        sticky.setAttribute('data-sticky', 'fixed');
                        sticky.style.width = width;
                    } else {
                        sticky.setAttribute('data-sticky', '');
                        sticky.removeAttribute('style');
                    }
                });
            });
        }
    });
})(document, window);
