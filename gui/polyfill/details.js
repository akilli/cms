/**
 * Details polyfill
 */
'use strict';

(function (document) {
    document.addEventListener('DOMContentLoaded', function () {
        const details = document.getElementsByTagName('details');

        if (details.length <= 0 || typeof details[0].open === 'boolean') {
            return;
        }

        document.documentElement.setAttribute('data-shim-details', '');

        [].forEach.call(details, function (item) {
            // Define open property
            Object.defineProperty(item, 'open', {
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
            let summary = item.firstElementChild;

            if (!summary || summary.tagName.toLowerCase() !== 'summary') {
                summary = document.createElement('summary');
                summary.innerText = 'Summary';
                item.insertBefore(summary, item.firstChild);
            }

            summary.addEventListener('click', function () {
                this.parentNode.open = !this.parentNode.hasAttribute('open');
            });

            // Wrap text nodes
            let b = 0;
            let child;
            let span;

            while (child = item.childNodes[b++]) {
                if (child.nodeType === 3 && /[^\t\n\r ]/.test(child.data)) {
                    span = document.createElement('span');
                    item.insertBefore(span, child);
                    span.textContent = child.data;
                    item.removeChild(child);
                }
            }
        });
    });
})(document);
