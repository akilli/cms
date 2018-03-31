'use strict';

(function (document) {
    document.addEventListener('DOMContentLoaded', function () {
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
    });
})(document, window);
