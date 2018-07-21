/**
 * File Browser
 */
'use strict';

(function (document, window, app, CKEDITOR) {
    document.addEventListener('DOMContentLoaded', function () {
        // Browser window
        if (window.opener) {
            Array.prototype.forEach.call(document.querySelectorAll('span[data-act=select]'), function (item) {
                item.addEventListener('click', function () {
                    window.opener.postMessage({
                        id: item.getAttribute('data-id'),
                        src: item.getAttribute('data-url')
                    }, window.opener.origin);
                });
            });
            return;
        }

        // Main window
        const suffix = '-file';

        Array.prototype.forEach.call(document.querySelectorAll('span[data-act=browser]'), function (item) {
            item.addEventListener('click', function () {
                const ent = this.parentElement.getAttribute('data-type') || 'file';
                const url = '/' + ent + '/browser';
                const call = function (data) {
                    if (!data.id) {
                        return;
                    }

                    const id = item.getAttribute('data-id');
                    const input = document.getElementById(id);
                    const div = document.getElementById(id + suffix);
                    const ext = data.src.split('.').pop();
                    const type = ext && app.cfg.file.hasOwnProperty(ext) ? app.cfg.file[ext] : 'a';
                    const file = document.createElement(type);

                    while (div.firstChild) {
                        div.removeChild(div.firstChild);
                    }

                    input.setAttribute('value', data.id);
                    file.setAttribute(type === 'a' ? 'href' : 'src', data.src);
                    div.appendChild(file);
                };

                CKEDITOR.mediabrowser.open(url, call)
            });
        });
        Array.prototype.forEach.call(document.querySelectorAll('span[data-act=remove]'), function (item) {
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
