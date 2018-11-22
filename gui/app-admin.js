/**
 * Admin Listeners
 */
'use strict';

(function (window, document, app, CKEDITOR) {
    /**
     * Delete Confirmation
     */
    function deleteConfirmation() {
        Array.prototype.forEach.call(document.querySelectorAll('a[data-action=delete]'), function (item) {
            item.addEventListener('click', function (event) {
                if (!confirm(app.i18n('Please confirm delete operation'))) {
                    event.preventDefault();
                }
            });
        });
    }

    /**
     * Input password autocomplete fix
     */
    function passwordAutocomplete() {
        Array.prototype.forEach.call(document.querySelectorAll('input[type=password]'), function (item) {
            item.setAttribute('readonly', 'readonly');
            item.addEventListener('focus', function () {
                this.removeAttribute('readonly');
            });
        });
    }

    /**
     * Rich Text Editor
     */
    function rte() {
        Array.prototype.forEach.call(document.querySelectorAll('textarea[data-type=rte]'), function (item) {
            CKEDITOR.replace(item, app.rte);
        });
    }

    /**
     * Media Browser
     */
    function mediaBrowser() {
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
    }

    /**
     * Media Browser Opener
     */
    function mediaBrowserOpen () {
        const suffix = '-file';

        Array.prototype.forEach.call(document.querySelectorAll('span[data-action=browser]'), function (item) {
            item.addEventListener('click', function () {
                const attrType = this.parentElement.getAttribute('data-type') || 'file';
                const entity = attrType === 'file' ? 'file' : 'file_' + attrType;
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
    }

    /**
     * Event Listener
     */
    document.addEventListener('DOMContentLoaded', function () {
        deleteConfirmation();
        passwordAutocomplete();
        rte();

        if (window.opener) {
            mediaBrowser();
        } else {
            mediaBrowserOpen();
        }
    });
})(window, document, app, CKEDITOR);
