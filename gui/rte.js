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
        removeDialogTabs: 'link:advanced;link:target',
        stylesSet: false,
        toolbar: [
            {name: 'clipboard', items: ['Undo', 'Redo']},
            {name: 'styles', items: ['Format']},
            {name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'RemoveFormat']},
            {name: 'links', items: ['Link', 'Unlink']},
            {name: 'paragraph', items: ['BulletedList', 'NumberedList', 'Blockquote']},
            {name: 'insert', items: ['Detail', 'Media', 'Table', 'HorizontalRule']},
            {name: 'tools', items: ['ShowBlocks']}
        ]
    };

    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('textarea[data-type=rte]'), function (item) {
            CKEDITOR.replace(item, cfg);
        });
    });
})(document, CKEDITOR);
