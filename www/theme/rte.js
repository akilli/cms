'use strict';

(function (document, CKEDITOR) {
    document.addEventListener('DOMContentLoaded', () => {
        const cfg = {
            customConfig: '',
            disableNativeSpellChecker: true,
            extraPlugins: 'media',
            filebrowserBrowseUrl: '/media/browser',
            filebrowserImageBrowseUrl: '/media/browser/image',
            height: '30rem',
            mediaURL: '/media/browser',
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
        const rte = document.querySelectorAll('[data-type=rte]');

        for (let i = 0; i < rte.length; i++) {
            if (rte[i].tagName.toLowerCase() === 'textarea') {
                CKEDITOR.replace(rte[i], cfg);
            } else {
                rte[i].setAttribute('contenteditable', true);
                CKEDITOR.inline(rte[i], cfg);
            }
        }
    });
})(document, CKEDITOR);
