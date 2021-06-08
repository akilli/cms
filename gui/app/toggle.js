/**
 * Toggle
 *
 * @type {Function}
 */
export default function () {
    document.addEventListener('DOMContentLoaded', () =>
        document.querySelectorAll('a[data-action=toggle]').forEach(a => {
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
        })
    );
}
