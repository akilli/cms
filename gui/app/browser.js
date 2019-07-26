import Browser from '../editor/src/util/Browser.js';
import Media from '../editor/src/util/Media.js';

/**
 * Browser + Opener
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

        document.addEventListener('DOMContentLoaded', () => document.querySelectorAll(':root[data-action=browser] #content .block-index article').forEach(item => {
            const msg = {};
            Object.getOwnPropertyNames(item.dataset).forEach(name => msg[name] = item.dataset[name]);
            item.addEventListener('click', () => window.opener.postMessage(msg, origin));
        }));
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

                Browser.open(window, `/${entity}/browser`, data => {
                    if (!data.id) {
                        return;
                    }

                    const id = item.getAttribute('data-id');
                    const input = document.getElementById(id);
                    const div = document.getElementById(id + suffix);
                    const type = Media.fromUrl(data.src);
                    const typeEl = type ? type.element : 'a';
                    const file = document.createElement(typeEl);

                    while (div.firstChild) {
                        div.removeChild(div.firstChild);
                    }

                    input.setAttribute('value', data.id);

                    if (['audio', 'video'].includes(type)) {
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
