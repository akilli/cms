'use strict';

(function (CKEDITOR) {
    CKEDITOR.plugins.add('media', {
        requires: 'widget,dialog',
        icons: 'media',
        lang: 'de,en',
        init: function(editor) {
            editor.widgets.add('media', {
                button: editor.lang.media.title,
                dialog: 'media',
                template:
                    '<figure class="media">' +
                        '<img src="{src}" alt="{alt}" />' +
                        '<figcaption>{caption}</figcaption>' +
                    '</figure>',
                editables: {
                    media: {
                        selector: 'img'
                    },
                    caption: {
                        selector: 'figcaption',
                        allowedContent: 'strong em'
                    }
                },
                allowedContent: 'figure(!media); img[!src, alt]; figcaption',
                requiredContent: 'figure(media); img[src]',
                upcast: function(element) {
                    return element.name == 'figure' && element.hasClass('media');
                }
            });

            CKEDITOR.dialog.add('media', this.path + 'dialogs/media.js');
        }
    });
})(CKEDITOR);
