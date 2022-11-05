import i18n from './i18n.js';

/**
 * Delete Confirmation
 *
 * @type {function}
 */
export default function () {
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('a[data-action=delete]').forEach(item => item.addEventListener('click', ev => {
            if (!confirm(i18n('Please confirm delete operation'))) {
                ev.preventDefault();
            }
        }));
    });
}
