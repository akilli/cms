/**
 * Details polyfill
 */
export default function () {
    document.addEventListener('DOMContentLoaded', () => {
        const all = document.getElementsByTagName('details');

        if (all.length <= 0 || typeof all[0].open === 'boolean') {
            return;
        }

        document.documentElement.setAttribute('data-details', '');

        [].forEach.call(all, details => {
            // Define open property
            Object.defineProperty(details, 'open', {
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
            let summary = details.firstElementChild;

            if (!summary || summary.tagName.toLowerCase() !== 'summary') {
                summary = document.createElement('summary');
                summary.innerText = 'Summary';
                details.insertAdjacentElement('afterbegin', summary);
            }

            summary.addEventListener('click', () => details.open = !details.hasAttribute('open'));

            // Wrap text nodes
            let b = 0;
            let child;
            let span;

            while (child = details.childNodes[b++]) {
                if (child.nodeType === Node.TEXT_NODE && /[^\t\n\r ]/.test(child.data)) {
                    span = document.createElement('span');
                    details.insertBefore(span, child);
                    span.textContent = child.data;
                    details.removeChild(child);
                }
            }
        });
    });
}
