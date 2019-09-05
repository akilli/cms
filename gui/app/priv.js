/**
 * Privileges
 *
 * @type {Function}
 */
export default function () {
    const sel = 'input[type=checkbox][multiple]';

    document.addEventListener('DOMContentLoaded', () => document.querySelectorAll(`:root[data-entity=role][data-action=edit] ${sel}#attr-priv-_all_`).forEach(all => {
        const call = () => all.form.querySelectorAll(`${sel}[name="${all.name}"]:not(#attr-priv-_all_)`).forEach(item => {
            if (all.checked) {
                item.checked = false;
                item.setAttribute('disabled', '');
            } else {
                item.removeAttribute('disabled');
            }
        });
        all.addEventListener('change', call);
        call();
    }));
}
