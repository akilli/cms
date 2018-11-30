/**
 * Rich Text Editor
 */
'use strict';

(function (document, app, CKEDITOR) {
    const cfg = {
        contentsCss: '',
        customConfig: '',
        disableNativeSpellChecker: true,
        extraAllowedContent: 'article section(*)',
        fillEmptyBlocks: false,
        format_tags: 'p;h2;h3',
        height: '30rem',
        language: 'de',
        mediabrowserUrl: '/file/browser',
        removeDialogTabs: 'link:advanced;link:target',
        stylesSet: false,
        toolbar: [
            {
                name: 'all',
                items: ['Undo', 'Redo', 'Bold', 'Italic', 'Link', 'Unlink', 'Format', 'BulletedList', 'NumberedList', 'Blockquote', 'Media', 'Table', 'Detail']
            }
        ]
    };

    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('textarea[data-type=rte]'), function (item) {
            CKEDITOR.replace(item, cfg);
        });
    });
})(document, app, CKEDITOR);
