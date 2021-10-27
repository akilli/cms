/**
 * Privileges
 *
 * @type {Function}
 */
export default function () {
    const sel = 'input[type=checkbox][multiple][name="privilege[]"]';
    const allSel = `:root[data-entity=role]:is([data-action=add], [data-action=edit]) ${sel}[value=_all_]`;

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll(allSel).forEach(all => {
            const call = () => all.form.querySelectorAll(`${sel}:not([value=_all_])`).forEach(item => {
                if (all.checked) {
                    item.checked = false;
                    item.setAttribute('disabled', '');
                } else {
                    item.removeAttribute('disabled');
                }
            });
            all.addEventListener('change', call);
            call();
        });
    });
}
