CKEDITOR.plugins.add('media', {
    icons: 'media',
    lang: 'de,en',
    init: function(editor) {
        editor.addCommand('media', new CKEDITOR.dialogCommand('mediaDialog', {
            allowedContent: 'img[src,alt]',
            requiredContent: 'img',
        }));
        editor.ui.addButton('media', {
            label: editor.lang.media.title,
            command: 'media',
            toolbar: 'insert'
        });

        CKEDITOR.dialog.add('mediaDialog', this.path + 'dialogs/media.js');
    }
});
