(function (document, CKEDITOR) {
    'use strict';

    document.addEventListener('DOMContentLoaded', function()
    {
        // Rich Text Editor
        const rte = document.querySelectorAll('[data-type=rte]');

        for (let i = 0; i < rte.length; i++) {
            CKEDITOR.replace(rte[i]);
        }

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
            del[i].addEventListener('click', function (event) {
                if (!confirm(app._('Please confirm delete operation'))) {
                    event.preventDefault();
                }
            })
        }
    });
})(document, CKEDITOR);
