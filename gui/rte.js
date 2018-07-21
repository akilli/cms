/**
 * Rich Text Editor
 */
'use strict';

(function (document, CKEDITOR) {
    const cfg = {
        customConfig: '',
        disableNativeSpellChecker: true,
        extraAllowedContent: 'article section(*)',
        format_tags: 'p;h2;h3',
        height: '30rem',
        language: 'de',
        mediabrowserUrl: '/file/browser',
        removeDialogTabs: 'link:advanced;link:target',
        stylesSet: false,
        toolbar: [
            {
                name: 'all',
                items: [
                    'Undo',
                    'Redo',
                    'Format',
                    'Bold',
                    'Italic',
                    'RemoveFormat',
                    'Link',
                    'Unlink',
                    'BulletedList',
                    'NumberedList',
                    'Blockquote',
                    'Detail',
                    'Media',
                    'Table',
                    'HorizontalRule',
                    'ShowBlocks'
                ]
            }
        ]
    };

    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('textarea[data-type=rte]'), function (item) {
            CKEDITOR.replace(item, cfg);
        });
    });
})(document, CKEDITOR);
