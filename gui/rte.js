/**
 * Rich Text Editor
 */
'use strict';

(function (document, CKEDITOR) {
    const cfg = {
        customConfig: '',
        disableNativeSpellChecker: true,
        extraAllowedContent: 'article section(*)',
        filebrowserBrowseUrl: '/file/browser',
        format_tags: 'p;h2;h3',
        height: '30rem',
        removeButtons: 'Cut,Copy,Paste,Undo,Redo,Anchor,Strike,Subscript,Superscript,Indent,Outdent',
        removeDialogTabs: 'link:advanced;link:target',
        stylesSet: false,
        toolbarGroups: [
            {name: 'document', groups: ['mode', 'document', 'doctools']},
            {name: 'clipboard', groups: ['clipboard', 'undo']},
            {name: 'editing', groups: ['find', 'selection', 'spellchecker']},
            {name: 'forms'},
            {name: 'styles'},
            {name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
            {name: 'links'},
            {name: 'paragraph', groups: ['list', 'blocks', 'indent', 'align', 'bidi']},
            {name: 'insert'},
            {name: 'colors'},
            {name: 'tools'},
            {name: 'others'},
            {name: 'about'}
        ]
    };

    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('textarea[data-type=rte]'), function (item) {
            CKEDITOR.replace(item, cfg);
        });
    });
})(document, CKEDITOR);
