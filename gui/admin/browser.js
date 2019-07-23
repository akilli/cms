/**
 * Browser + Opener
 */
'use strict';

((window, document, app, CKEDITOR) => {
    /**
     * Browser
     */
    function browser() {
        let origin;

        try {
            origin = window.opener.origin || window.opener.location.origin;
        } catch (e) {
            document.body.innerHTML = app.i18n('Page not found');
            setTimeout(window.close, 3000);
            return;
        }

        document.querySelectorAll(':root[data-action=browser] #content .block-index article').forEach(item => {
            const msg = {};
            Object.getOwnPropertyNames(item.dataset).forEach(name => msg[name] = item.dataset[name]);
            item.addEventListener('click', () => window.opener.postMessage(msg, origin));
        });
    }

    /**
     * Opener
     */
    function open() {
        const suffix = '-file';

        document.querySelectorAll('a[data-action=browser][data-ref]').forEach(item => {
            item.addEventListener('click', () => {
                const entity = item.getAttribute('data-ref');

                if (!entity) {
                    return;
                }

                CKEDITOR.api.browser(`/${entity}/browser`, data => {
                    if (!data.id) {
                        return;
                    }

                    const id = item.getAttribute('data-id');
                    const input = document.getElementById(id);
                    const div = document.getElementById(id + suffix);
                    const type = CKEDITOR.api.media.fromUrl(data.src);
                    const typeEl = type ? CKEDITOR.api.media.element(type) : 'a';
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
            });
        });
        document.querySelectorAll('a[data-action=remove]').forEach(item => {
            item.addEventListener('click', () => {
                const id = item.getAttribute('data-id');
                const input = document.getElementById(id);
                const div = document.getElementById(id + suffix);

                while (div.firstChild) {
                    div.removeChild(div.firstChild);
                }

                input.setAttribute('value', '');
            });
        });
    }

    /**
     * Event Listener
     */
    document.addEventListener('DOMContentLoaded', window.opener ? browser : open);
})(window, document, app, CKEDITOR);
