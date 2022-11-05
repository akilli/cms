/**
 * Navigation
 *
 * @type {function}
 */
export default function () {
    document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('#toolbar, #menu').forEach(menu => {
        menu.addEventListener('click', ev => {
            if (ev.target === menu && menu.hasAttribute('data-open')) {
                menu.removeAttribute('data-open');
            } else if (ev.target === menu) {
                menu.setAttribute('data-open', '');
                menu.scrollIntoView(true);
            }
        });
        menu.querySelectorAll('a[aria-haspopup=true]').forEach(a => {
            a.addEventListener('click', () => {
                const val = a.getAttribute('aria-expanded') === 'true' ? 'false' : 'true';
                a.setAttribute('aria-expanded', val);
            });
        });
    }));
}
