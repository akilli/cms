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
        const suffix = '-file';

        document.addEventListener('DOMContentLoaded', () => {
            // Open browser
            document.querySelectorAll('a[data-action=browser][data-ref]').forEach(item => item.addEventListener('click', () => {
                const entity = item.getAttribute('data-ref');

                if (!entity) {
                    return;
                }

                browser(`/${entity}/index`, async data => {
                    if (!data.id) {
                        return;
                    }

                    const id = item.getAttribute('data-id');
                    const input = document.getElementById(id);
                    const div = document.getElementById(id + suffix);
                    let typeEl = 'a';

                    if (data.type === 'image') {
                        typeEl = 'img';
                    } else if (['audio', 'iframe', 'video'].includes(data.type)) {
                        typeEl = data.type;
                    }

                    const file = document.createElement(typeEl);

                    while (div.firstChild) {
                        div.removeChild(div.firstChild);
                    }

                    input.setAttribute('value', data.id);

                    if (['audio', 'video'].includes(typeEl)) {
                        file.setAttribute('controls', 'controls');
                    }

                    if (typeEl === 'a') {
                        file.setAttribute('href', data.src);
                        file.innerText = data.src;
                    } else {
                        file.setAttribute('src', data.src);
                    }

                    div.appendChild(file);
                });
            }));
            // Remove selected item
            document.querySelectorAll('a[data-action=remove]').forEach(item => item.addEventListener('click', () => {
                const id = item.getAttribute('data-id');
                const input = document.getElementById(id);
                const div = document.getElementById(id + suffix);

                while (div.firstChild) {
                    div.removeChild(div.firstChild);
                }

                input.setAttribute('value', '');
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
