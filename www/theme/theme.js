(function (document, app, ClassicEditor) {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        // Input password autocomplete fix
        const pwd = document.querySelectorAll('input[type=password]');

        for (let i = 0; i < pwd.length; i++) {
            pwd[i].setAttribute('readonly', true);
            pwd[i].addEventListener('focus', function () {
                this.removeAttribute('readonly');
            })
        }

        // Delete buttons and links
        const del = document.querySelectorAll('.delete');

        for (let i = 0; i < del.length; i++) {
            del[i].addEventListener('click', event => {
                if (!confirm(app.i18n('Please confirm delete operation'))) {
                    event.preventDefault();
                }
            })
        }

        // Rich Text Editor
        const rte = document.querySelectorAll('[data-type=rte]');

        for (let i = 0; i < rte.length; i++) {
            ClassicEditor
                .create(rte[i], {
                    heading: {
                        options: [
                            {modelElement: 'paragraph', title: 'Paragraph'},
                            {modelElement: 'heading1', viewElement: 'h1', title: 'Heading 1'},
                            {modelElement: 'heading2', viewElement: 'h2', title: 'Heading 2'},
                            {modelElement: 'heading3', viewElement: 'h3', title: 'Heading 3'}
                        ]
                    }
                })
                .catch(error => {
                    console.error(error);
                });
        }
    });
})(document, app, ClassicEditor);
