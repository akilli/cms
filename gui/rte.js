'use strict';

(function (document, CKEDITOR) {
    document.addEventListener('DOMContentLoaded', function () {
        // RTE
        Array.prototype.forEach.call(document.querySelectorAll('textarea[data-type=rte]'), function (item) {
            const cfg = {
                customConfig: '',
                disableNativeSpellChecker: true,
                extraAllowedContent: 'article section(*)',
                filebrowserBrowseUrl: '/file/browser',
                format_tags: 'p;h1;h2;h3',
                height: '30rem',
                removeButtons: 'Cut,Copy,Paste,Undo,Redo,Anchor,Strike,Indent,Outdent',
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
            CKEDITOR.replace(item, cfg);
        });
    });
})(document, CKEDITOR);
