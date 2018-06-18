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
        removeDialogTabs: 'link:advanced',
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
                    'Underline',
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
