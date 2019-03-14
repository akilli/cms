/**
 * Rich Text Editor
 */
'use strict';

(function (document, CKEDITOR) {
    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('textarea[data-type=rte]'), function (item) {
            CKEDITOR.replace(item, {
                contentsCss: '',
                customConfig: '',
                disableNativeSpellChecker: true,
                extraAllowedContent: 'article section(*)',
                fillEmptyBlocks: false,
                format_tags: 'p;h2;h3',
                height: '30rem',
                language: document.documentElement.getAttribute('lang') || 'en',
                mediabrowserUrl: '/file_media/browser',
                removeDialogTabs: 'link:advanced;link:target',
                stylesSet: false,
                toolbar: [
                    {
                        name: 'all',
                        items: ['Undo', 'Redo', 'Bold', 'Italic', 'Link', 'Unlink', 'Format', 'BulletedList', 'NumberedList', 'Blockquote', 'Media', 'Table', 'Detail', 'HorizontalRule']
                    }
                ]
            });
        });
    });
})(document, CKEDITOR);
