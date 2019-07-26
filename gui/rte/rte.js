/**
 * Rich Text Editor
 */
'use strict';

((document, app, CKEDITOR) => {
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('textarea[data-type=rte]').forEach(rte => {
            CKEDITOR.replace(rte, {
                blockApi: id => typeof id === 'string' && id ? `${app.cfg.rte.blockapi}/${id}` : null,
                blockBrowser: app.cfg.rte.blockbrowser,
                contentsCss: app.cfg.rte.css,
                customConfig: '',
                disableNativeSpellChecker: true,
                fillEmptyBlocks: false,
                format_tags: 'p;h2;h3',
                height: '30rem',
                language: app.cfg.lang,
                mediaBrowser: app.cfg.rte.mediabrowser,
                removeDialogTabs: 'link:advanced;link:target',
                section: app.cfg.rte.section,
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
                            'Grid',
                            'Gallery',
                        ]
                    }
                ],
            });
        });
    });
})(document, app, CKEDITOR);
