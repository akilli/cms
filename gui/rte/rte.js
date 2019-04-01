/**
 * Rich Text Editor
 */
'use strict';

(function (document, app, CKEDITOR) {
    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('textarea[data-type=rte]'), function (item) {
            CKEDITOR.replace(item, {
                blockApi: id => typeof id === 'string' && id ? '/block/api/' + id : null,
                blockBrowser: '/block/browser',
                contentsCss: ['/gui/base.css', '/gui/rte/rte.css'],
                customConfig: '',
                disableNativeSpellChecker: true,
                fillEmptyBlocks: false,
                format_tags: 'p;h2;h3',
                height: '30rem',
                language: document.documentElement.getAttribute('lang') || 'en',
                mediaBrowser: '/file_media/browser',
                removeDialogTabs: 'link:advanced;link:target',
                section: {
                    'block-content': app.i18n('Content Block'),
                    'block-info': app.i18n('Info Block'),
                    'block-teaser': app.i18n('Block Teaser')
                },
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
})(document, app, CKEDITOR);
