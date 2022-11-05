/**
 * Print Listeners
 *
 * @type {function}
 */
export default function () {
    window.addEventListener('beforeprint', printBefore);
    window.addEventListener('afterprint', printAfter);
}

/**
 * Prepares print version
 */
function printBefore() {
    Array.from(document.getElementsByTagName('details')).forEach(details => {
        if (details.hasAttribute('open')) {
            details.setAttribute('data-open', '');
        } else {
            details.setAttribute('open', '');
        }
    });

    document.querySelectorAll('a[href^="/"]').forEach(a => {
        a.setAttribute('data-href', a.getAttribute('href'));
        a.setAttribute('href', a.href);
    });
}

/**
 * Restores screen version
 */
function printAfter() {
    Array.from(document.getElementsByTagName('details')).forEach(details => {
        if (details.hasAttribute('data-open')) {
            details.removeAttribute('data-open');
        } else {
            details.removeAttribute('open');
        }
    });

    document.querySelectorAll('a[data-href]').forEach(a => {
        a.setAttribute('href', a.getAttribute('data-href'));
        a.removeAttribute('data-href');
    });
}
