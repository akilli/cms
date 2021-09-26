/**
 * Browser + Opener
 *
 * @type {Object.<String, Function>}
 */
export default {
    /**
     * Browser window
     */
    win() {
        let origin;

        try {
            origin = window.opener.origin || window.opener.location.origin;
        } catch (e) {
            window.close();
            return;
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.documentElement.setAttribute('data-browser', '');
            document.querySelectorAll('#content .block-index article').forEach(item => {
                const msg = {};
                Object.getOwnPropertyNames(item.dataset).forEach(name => msg[name] = item.dataset[name]);
                item.addEventListener('click', () => window.opener.postMessage(msg, origin));
            });
        });
    },

    /**
     * Browser opener
     */
    open() {
        const suffix = '-output';

        document.addEventListener('DOMContentLoaded', () => {
            // Open browser
            document.querySelectorAll('a[data-action=browser][data-ref]').forEach(
                item => item.addEventListener('click', () => {
                    const entity = item.getAttribute('data-ref');

                    if (entity) {
                        browser(`/:${entity}/index`, async data => {
                            if (data.id) {
                                const id = item.getAttribute('data-id');
                                document.getElementById(id).setAttribute('value', data.id);
                                document.getElementById(id + suffix).textContent = data.name || data.id;
                            }
                        });
                    }
                })
            );
            // Remove selected item
            document.querySelectorAll('a[data-action=remove]').forEach(item => item.addEventListener('click', () => {
                const id = item.getAttribute('data-id');
                document.getElementById(id).setAttribute('value', '');
                document.getElementById(id + suffix).textContent = '';
            }));
        });
    },
}

/**
 * Browser window options
 *
 * @type {String}
 */
const browserOpts = Object.entries({
    alwaysRaised: 'yes',
    dependent: 'yes',
    height: `${window.screen.height}`,
    location: 'no',
    menubar: 'no',
    minimizable: 'no',
    modal: 'yes',
    resizable: 'yes',
    scrollbars: 'yes',
    toolbar: 'no',
    width: `${window.screen.width}`,
}).map(x => `${x[0]}=${x[1]}`).join(',');

/**
 * Opens a media browser window and registers a listener for communication between editor and browser windows
 *
 * @param {String} url
 * @param {Function} call
 */
function browser(url, call) {
    if (!url || typeof call !== 'function') {
        return;
    }

    const win = window.open(url, 'browser', browserOpts);
    const a = document.createElement('a');
    a.href = url;
    const origin = a.origin;

    window.addEventListener('message', ev => {
        if (ev.origin === origin && ev.source === win) {
            call(ev.data);
            win.close();
        }
    }, false);
}
