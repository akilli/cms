/**
 * File Browser
 */
'use strict';

(function (document, window, app, CKEDITOR) {
    // Browser window
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.opener) {
            return;
        }

        let origin;

        try {
            origin = window.opener.origin;
        } catch (e) {
            document.body.innerHTML = app.i18n('Page not found');
            setTimeout(window.close, 3000);
            return;
        }

        Array.prototype.forEach.call(document.querySelectorAll('span[data-action=select]'), function (item) {
            item.addEventListener('click', function () {
                window.opener.postMessage({
                    id: item.getAttribute('data-id'),
                    src: item.getAttribute('data-url')
                }, origin);
            });
        });
    });

    // Main window
    document.addEventListener('DOMContentLoaded', function () {
        const suffix = '-file';

        Array.prototype.forEach.call(document.querySelectorAll('span[data-action=browser]'), function (item) {
            item.addEventListener('click', function () {
                const entity = this.parentElement.getAttribute('data-type') || 'file';
                const url = '/' + entity + '/browser';
                const call = function (data) {
                    if (!data.id) {
                        return;
                    }

                    const id = item.getAttribute('data-id');
                    const input = document.getElementById(id);
                    const div = document.getElementById(id + suffix);
                    const type = CKEDITOR.media.getTypeFromUrl(data.src);
                    const typeEl = type ? CKEDITOR.media.getTypeElement(type) : 'a';
                    const file = document.createElement(typeEl);

                    while (div.firstChild) {
                        div.removeChild(div.firstChild);
                    }

                    input.setAttribute('value', data.id);
                    file.setAttribute(typeEl === 'a' ? 'href' : 'src', data.src);
                    div.appendChild(file);
                };

                CKEDITOR.mediabrowser.open(url, call)
            });
        });
        Array.prototype.forEach.call(document.querySelectorAll('span[data-action=remove]'), function (item) {
            item.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const input = document.getElementById(id);
                const div = document.getElementById(id + suffix);

                while (div.firstChild) {
                    div.removeChild(div.firstChild);
                }

                input.setAttribute('value', '');
            });
        });
    });
})(document, window, app, CKEDITOR);
