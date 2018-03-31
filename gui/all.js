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
        const gallery = document.querySelectorAll('.gallery .items > a');

        for (let a = 0; a < gallery.length; a++) {
            gallery[a].addEventListener('click', function (e) {
                let dialog = document.getElementById('dialog');
                let button;
                let img;

                if (!dialog) {
                    dialog = document.createElement('dialog');
                    dialog.id = 'dialog';
                    body.appendChild(dialog);
                    button = document.createElement('button');
                    button.innerText = 'X';
                    dialog.appendChild(button);
                    img = document.createElement('img');
                    dialog.appendChild(img);
                } else {
                    button = dialog.getElementsByTagName('button')[0];
                    img = dialog.getElementsByTagName('img')[0];
                }

                button.addEventListener('click', function () {
                    dialog.removeAttribute('open');
                });
                img.setAttribute('src', this.getAttribute('href'));
                dialog.setAttribute('open', '');
                e.preventDefault();
            })
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
