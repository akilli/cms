'use strict';

(function (document, window) {
    function printBefore() {
        // Details
        const details = document.getElementsByTagName('details');

        for (let a = 0; a < details.length; a++) {
            details[a].setAttribute('open', '');
        }

        // Links
        const link = document.querySelectorAll('a[href^="/"]');

        for (let a = 0; a < link.length; a++) {
            link[a].setAttribute('data-href', link[a].getAttribute('href'));
            link[a].setAttribute('href', link[a].href);
        }
    }

    function printAfter() {
        // Details
        const details = document.getElementsByTagName('details');

        for (let a = 0; a < details.length; a++) {
            details[a].removeAttribute('open');
        }

        // Links
        const link = document.querySelectorAll('a[data-href]');

        for (let a = 0; a < link.length; a++) {
            link[a].setAttribute('href', link[a].getAttribute('data-href'));
            link[a].removeAttribute('data-href')
        }
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

        // Multi-checkbox required fix
        const form = document.getElementsByTagName('form');

        for (let a = 0; a < form.length; a++) {
            let sel = 'input[type=checkbox][multiple]';
            let multi = form[a].querySelectorAll(sel + '[required]');

            for (let b = 0; b < multi.length; b++) {
                multi[b].addEventListener('change', function () {
                    let req = !!this.form.querySelector(sel + '[name="' + this.name + '"]:checked');
                    let sib = this.form.querySelectorAll(sel + '[name="' + this.name + '"]');

                    for (let c = 0; c < sib.length; c++) {
                        if (req) {
                            sib[c].removeAttribute('required');
                        } else {
                            sib[c].setAttribute('required', 'required');
                        }
                    }
                });
            }
        }

        // Gallery dialog
        const body = document.getElementsByTagName('body')[0];
        const gallery = document.querySelectorAll('.gallery .items > *');

        for (let a = 0; a < gallery.length; a++) {
            gallery[a].addEventListener('click', function (e) {
                if (!!document.getElementById('dialog')) {
                    document.getElementById('dialog').parentElement.removeChild(document.getElementById('dialog'));
                }

                let current = this;
                const dialog = document.createElement('dialog');
                const img = document.createElement('img');
                const close = document.createElement('button');
                const prev = document.createElement('button');
                const next = document.createElement('button');

                // Dialog
                dialog.id = 'dialog';
                dialog.addEventListener('click', function (e) {
                    if (e.target === this) {
                        dialog.parentElement.removeChild(this);
                    }
                });
                body.appendChild(dialog);
                // Close button
                close.setAttribute('data-act', 'close');
                close.innerText = 'x';
                close.addEventListener('click', function () {
                    dialog.parentElement.removeChild(dialog);
                });
                dialog.appendChild(close);
                // Prev button
                prev.setAttribute('data-act', 'prev');
                prev.innerText = '<';
                prev.addEventListener('click', function () {
                    const ref = current.previousElementSibling || current.parentElement.lastElementChild;
                    img.setAttribute('src', ref.getAttribute('href'));
                    current = ref;
                });
                dialog.appendChild(prev);
                // Next button
                next.setAttribute('data-act', 'next');
                next.innerText = '>';
                next.addEventListener('click', function () {
                    const ref = current.nextElementSibling || current.parentElement.firstElementChild;
                    img.setAttribute('src', ref.getAttribute('href'));
                    current = ref;
                });
                dialog.appendChild(next);
                // Image
                img.setAttribute('src', this.getAttribute('href'));
                dialog.appendChild(img);
                // Open dialog
                dialog.setAttribute('open', '');
                e.preventDefault();
            });
        }
    });

    window.addEventListener('load', function () {
        // Sticky navigation polyfill
        const nav = document.querySelector('#menu.sticky');

        if (!!nav && window.getComputedStyle(nav).getPropertyValue('position') !== 'sticky') {
            setTimeout(function() {
                const pos = nav.offsetTop;
                const width = window.getComputedStyle(nav.parentElement).getPropertyValue('width');

                window.addEventListener('scroll', function () {
                    if (window.pageYOffset >= pos) {
                        nav.setAttribute('data-sticky', '');
                        nav.style.width = width;
                    } else {
                        nav.removeAttribute('data-sticky');
                        nav.removeAttribute('style');
                    }
                });
            });
        }
    });
})(document, window);
