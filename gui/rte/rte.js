/**
 * Rich Text Editor
 */
'use strict';

(function (document, CKEDITOR) {
    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('textarea[data-type=rte]'), function (item) {
            CKEDITOR.replace(item, {
                //blockApi: id => isNaN(id) ? null : '../api/' + id,
                //blockBrowser: '../browser/block.html',
                contentsCss: ['/gui/base.css', '/gui/rte/rte.css'],
                customConfig: '',
                disableNativeSpellChecker: true,
                fillEmptyBlocks: false,
                format_tags: 'p;h2;h3',
                height: '30rem',
                language: document.documentElement.getAttribute('lang') || 'en',
                mediaBrowser: '/file_media/browser',
                removeDialogTabs: 'link:advanced;link:target',
                section: {'block-content': 'Inhaltsblock', 'block-info': 'Infoblock'},
                stylesSet: false,
                toolbar: [
                    {
                        name: 'all',
                        items: [
                            'Undo',
                            'Redo',
                            'Maximize',
                            'Bold',
                            'Italic',
                            'Link',
                            'Unlink',
                            'Format',
                            'BulletedList',
                            'NumberedList',
                            'Quote',
                            'Media',
                            'Table',
                            'Detail',
                            'Section',
                            'Block',
                            'Grid'
                        ]
                    }
                ]
            });
        });
    });
})(document, CKEDITOR);
