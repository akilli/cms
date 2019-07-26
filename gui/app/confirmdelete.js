import app from './app.js';

/**
 * Delete Confirmation
 */
export default function () {
    document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('a[data-action=delete]').forEach(item => item.addEventListener('click', ev => {
        if (!confirm(app.i18n('Please confirm delete operation'))) {
            ev.preventDefault();
        }
    })));
}
