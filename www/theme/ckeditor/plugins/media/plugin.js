'use strict';

(function (CKEDITOR) {
    CKEDITOR.plugins.add('media', {
        requires: 'dialog,filebrowser,widget',
        icons: 'media',
        lang: 'de,en',
        init: function(editor) {
            editor.widgets.add('media', {
                button: editor.lang.media.title,
                dialog: 'media',
                template: '<figure class="media"><img src="" alt="" /><figcaption></figcaption></figure>',
                editables: {
                    caption: {
                        selector: 'figcaption',
                        allowedContent: 'strong em'
                    }
                },
                allowedContent: 'figure(!media); img audio video[!src, alt, controls]; figcaption',
                requiredContent: 'figure(media); img audio video[src]',
                upcast: function(element) {
                    return element.name == 'figure' && element.hasClass('media');
                }
            });

            CKEDITOR.dialog.add('media', this.path + 'dialogs/media.js');
        }
    });
})(CKEDITOR);
