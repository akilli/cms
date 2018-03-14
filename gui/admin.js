'use strict';

(function (window, document, app, CKEDITOR) {
    document.addEventListener('DOMContentLoaded', function () {
        // Input password autocomplete fix
        document.querySelectorAll('input[type=password]').forEach(function (item) {
            item.setAttribute('readonly', true);
            item.addEventListener('focus', function () {
                this.removeAttribute('readonly');
            });
        });

        // Delete buttons and links
        document.querySelectorAll('a[data-act=delete]').forEach(function (item) {
            item.addEventListener('click', function (event) {
                if (!confirm(app.i18n('Please confirm delete operation'))) {
                    event.preventDefault();
                }
            });
        });

        // Browser
        const suffix = '-file';

        document.querySelectorAll('span[data-act=browser]').forEach(function (item) {
            const ent = item.parentElement.getAttribute('data-type') || 'file';

            item.addEventListener('click', function () {
                window.open(
                    '/' + ent + '/browser?el=' + this.getAttribute('data-id'),
                    'browser',
                    'location=no,menubar=no,toolbar=no,dependent=yes,minimizable=no,modal=yes,alwaysRaised=yes,resizable=yes,scrollbars=yes'
                );
            });
        });

        document.querySelectorAll('span[data-act=remove]').forEach(function (item) {
            item.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const el = document.getElementById(id + suffix);

                document.getElementById(id).setAttribute('value', '');

                if (el) {
                    el.innerHTML = '';
                }
            });
        });

        // RTE browser
        document.querySelectorAll('span[data-act=select]').forEach(function (item) {
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
                        el.innerHTML = '';
                        el.appendChild(file);
                    }
                }

                window.close();
            });
        });

        // RTE
        const cfg = {
            customConfig: '',
            disableNativeSpellChecker: true,
            extraAllowedContent: 'div section(*)',
            filebrowserBrowseUrl: '/file/browser',
            format_tags: 'p;h1;h2;h3',
            height: '30rem',
            removeButtons: 'Cut,Copy,Paste,Undo,Redo,Anchor,Strike,Subscript,Superscript,Indent,Outdent',
            removeDialogTabs: 'link:advanced;link:target',
            stylesSet: false,
            toolbarGroups: [
                {name: 'document', groups: ['mode', 'document', 'doctools']},
                {name: 'clipboard', groups: ['clipboard', 'undo']},
                {name: 'editing', groups: ['find', 'selection', 'spellchecker']},
                {name: 'forms'},
                {name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
                {name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi']},
                {name: 'links'},
                {name: 'insert'},
                {name: 'styles'},
                {name: 'colors'},
                {name: 'tools'},
                {name: 'others'},
                {name: 'about'}
            ]
        };
        document.querySelectorAll('textarea[data-type=rte]').forEach(function (item) {
            CKEDITOR.replace(item, cfg);
        });
    });
})(window, document, app, CKEDITOR);
