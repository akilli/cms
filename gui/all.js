'use strict';

(function (document, window) {
    function detailsBefore() {
        const details = document.getElementsByTagName('details');

        for (let a = 0; a < details.length; a++) {
            details[a].setAttribute('open', '');
        }
    }

    function detailsAfter() {
        const details = document.getElementsByTagName('details');

        for (let a = 0; a < details.length; a++) {
            details[a].removeAttribute('open');
        }
    }

    function detailsShim() {
        const details = document.getElementsByTagName('details');

        if (details.length <= 0 || typeof details[0].open === 'boolean') {
            return;
        }

        document.documentElement.setAttribute('data-shim-details', 'true');

        for (let a = 0; a < details.length; a++) {
            // Define open property
            Object.defineProperty(details[a], 'open', {
                get: function () {
                    return this.hasAttribute('open');
                },
                set: function (state) {
                    if (state) {
                        this.setAttribute('open', '');
                    } else {
                        this.removeAttribute('open');
                    }
                }
            });

            // Summary element
            let summary = details[a].firstChild;

            if (!summary || summary.tagName.toLowerCase() !== 'summary') {
                summary = document.createElement('summary');
                summary.innerText = 'Summary';
                details[a].insertBefore(summary, details[a].firstChild);
            }

            summary.addEventListener('click', function () {
                this.parentNode.open = !this.parentNode.hasAttribute('open');
            });

            // Wrap text nodes
            let b = 0;
            let child;

            while (child = details[a].childNodes[b++]) {
                if (child.nodeType === 3 && /[^\t\n\r ]/.test(child.data)) {
                    let span = document.createElement('span');
                    details[a].insertBefore(span, child);
                    span.textContent = child.data;
                    details[a].removeChild(child);
                }
            }
        }
    }

    function cssBefore() {
        const css = document.querySelectorAll('link[rel=stylesheet]');

        for (let a = 0; a < css.length; a++) {
            if (!css[a].media || css[a].media === 'all') {
                continue;
            }

            if (css[a].media.indexOf('print') === -1) {
                css[a].disabled = true;
            } else {
                css[a].media = 'all';
                css[a].setAttribute('data-print', '');
            }
        }
    }

    function cssAfter() {
        const css = document.querySelectorAll('link[rel=stylesheet]');

        for (let a = 0; a < css.length; a++) {
            css[a].disabled = false;

            if (css[a].hasAttribute('data-print')) {
                css[a].media = 'print';
                css[a].removeAttribute('data-print');
            }
        }
    }

    function linkBefore() {
        const link = document.querySelectorAll('a[href^="/"]');

        for (let a = 0; a < link.length; a++) {
            link[a].setAttribute('data-href', link[a].getAttribute('href'));
            link[a].setAttribute('href', link[a].href);
        }
    }

    function linkAfter() {
        const link = document.querySelectorAll('a[data-href]');

        for (let a = 0; a < link.length; a++) {
            link[a].setAttribute('href', link[a].getAttribute('data-href'));
            link[a].removeAttribute('data-href')
        }
    }

    function pdfBefore() {
        detailsBefore();
        cssBefore();
        linkBefore();
        document.getElementById('content').style.width = '100%';
    }

    function pdfAfter() {
        document.getElementById('content').removeAttribute('style');
        linkAfter();
        cssAfter();
        detailsAfter();
    }

    document.addEventListener('DOMContentLoaded', function () {
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

        // Details
        detailsShim();

        const mql = window.matchMedia('print');
        mql.addListener(function (media) {
            if (media.matches) {
                detailsBefore();
            } else {
                detailsAfter();
            }
        });

        window.addEventListener('beforeprint', detailsBefore);
        window.addEventListener('afterprint', detailsAfter);

        // Print version
        const print = document.querySelectorAll('a[data-act=print]');

        for (let a = 0; a < print.length; a++) {
            print[a].addEventListener('click', function () {
                window.print();
            });
        }

        // PDF version
        const pdf = document.querySelectorAll('a[data-act=pdf]');
        const pdfFile = window.location.pathname.replace(/\//g, '-').replace(/\.html$/, '').replace(/^-/, '') || 'index';
        const pdfOpt = {
            margin: [14, 20, 13, 20],
            filename: pdfFile + '.pdf',
            image: {type: 'jpeg', quality: 0.98},
            html2canvas: {dpi: 192, letterRendering: true},
            jsPDF: {unit: 'mm', format: 'letter', orientation: 'portrait'},
            enableLinks: true
        };

        for (let a = 0; a < pdf.length; a++) {
            pdf[a].addEventListener('click', function (event) {
                event.preventDefault();
                pdfBefore();
                html2pdf().set(pdfOpt).from(document.getElementsByTagName('body')[0]).to('pdf').save().then(pdfAfter, pdfAfter);
            });
        }
    });

    window.addEventListener('load', function () {
        // Sticky navigation polyfill
        const nav = document.getElementById('menu');

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
