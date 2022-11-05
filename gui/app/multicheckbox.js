/**
 * Multi-checkbox required fix
 *
 * @type {function}
 */
export default function () {
    const sel = 'input[type=checkbox][multiple]';

    document.addEventListener('DOMContentLoaded', () => document.querySelectorAll(`${sel}[required]`).forEach(item => {
        item.addEventListener('change', () => {
            const req = item.form.querySelector(`${sel}[name="${item.name}"]:checked`);
            item.form.querySelectorAll(`${sel}[name="${item.name}"]`).forEach(sib => {
                req ? sib.removeAttribute('required') : sib.setAttribute('required', '');
            });
        });
    }));
}
