/**
 * Rich Text Editor
 */
'use strict';

(function (document, CKEDITOR) {
    document.addEventListener('DOMContentLoaded', function () {
        const lang = document.documentElement.getAttribute('lang') || 'en';
        const rte = {
            contentsCss: '',
            customConfig: '',
            disableNativeSpellChecker: true,
            extraAllowedContent: 'article section(*)',
            fillEmptyBlocks: false,
            format_tags: 'p;h2;h3',
            language: lang,
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
        const rtemin = {
            contentsCss: '',
            customConfig: '',
            disableNativeSpellChecker: true,
            fillEmptyBlocks: false,
            language: lang,
            removeDialogTabs: 'link:advanced;link:target',
            removePlugins: ['blockquote', 'detail', 'list', 'media', 'mediabrowser', 'table'],
            stylesSet: false,
            toolbar: [
                {
                    name: 'all',
                    items: ['Undo', 'Redo', 'Bold', 'Italic', 'Link', 'Unlink']
                }
            ]
        };

        Array.prototype.forEach.call(document.querySelectorAll('textarea[data-type=rte], textarea[data-type=rtemin]'), function (item) {
            CKEDITOR.replace(item, item.getAttribute('data-type') === 'rtemin' ? rtemin : rte);
        });
    });
})(document, CKEDITOR);
