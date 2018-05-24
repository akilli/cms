/**
 * File Browser
 */
'use strict';

(function (document, window, app) {
    document.addEventListener('DOMContentLoaded', function () {
        const suffix = '-file';

        Array.prototype.forEach.call(document.querySelectorAll('span[data-act=browser]'), function (item) {
            item.addEventListener('click', function () {
                const ent = this.parentElement.getAttribute('data-type') || 'file';
                window.open(
                    '/' + ent + '/browser?el=' + this.getAttribute('data-id'),
                    'browser',
                    'location=no,menubar=no,toolbar=no,dependent=yes,minimizable=no,modal=yes,alwaysRaised=yes,resizable=yes,scrollbars=yes'
                );
            });
        });
        Array.prototype.forEach.call(document.querySelectorAll('span[data-act=remove]'), function (item) {
            item.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const el = document.getElementById(id + suffix);

                document.getElementById(id).setAttribute('value', '');

                if (el) {
                    while (el.firstChild) {
                        el.removeChild(el.firstChild);
                    }
                }
            });
        });
        Array.prototype.forEach.call(document.querySelectorAll('span[data-act=select]'), function (item) {
            item.addEventListener('click', function () {
                if (!window.opener) {
                    return;
                }

                const doc = window.opener.document;
                const url = this.getAttribute('data-url');
                let attr;
                let el;

                if (attr = app.param('CKEditorFuncNum')) {
                    window.opener.CKEDITOR.tools.callFunction(attr, url);
                } else if ((attr = app.param('el')) && (el = doc.getElementById(attr))) {
                    el.setAttribute('value', this.getAttribute('data-id'));

                    if (el = doc.getElementById(attr + suffix)) {
                        const ext = url.split('.').pop();
                        const type = ext && app.cfg.file.hasOwnProperty(ext) ? app.cfg.file[ext] : 'a';
                        const file = doc.createElement(type);

                        file.setAttribute(type === 'a' ? 'href' : 'src', url);

                        while (el.firstChild) {
                            el.removeChild(el.firstChild);
                        }

                        el.appendChild(file);
                    }
                }

                window.close();
            });
        });
    });
})(document, window, app);
