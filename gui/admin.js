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

        // RTE browser
        document.querySelectorAll('span.rte').forEach(function (item) {
            item.addEventListener('click', function () {
                window.opener.CKEDITOR.tools.callFunction(this.getAttribute('data-rte'), this.getAttribute('data-url'));
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
