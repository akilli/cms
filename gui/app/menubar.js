/**
 * Navigation
 *
 * @type {Function}
 */
export default function () {
    document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('nav[role=menubar]').forEach(nav => {
        nav.addEventListener('click', ev => {
            if (ev.target === nav && nav.hasAttribute('data-open')) {
                nav.removeAttribute('data-open');
            } else if (ev.target === nav) {
                nav.setAttribute('data-open', '');
            }
        })
    }));
}
