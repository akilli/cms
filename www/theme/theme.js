(function (document, CKEDITOR) {
    'use strict';

    document.addEventListener('DOMContentLoaded', function()
    {
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
                if (!confirm(app.i18n('Please confirm delete operation'))) {
                    event.preventDefault();
                }
            })
        }

        // Vertical Rhythm Images
        const vr = 24;
        const img = document.querySelectorAll('#content img');

        for (let i = 0; i < img.length; i++) {
            let ratio = img[i].clientHeight > 0 ? img[i].clientWidth / img[i].clientHeight : 0;
            let vrh = parseInt(img[i].clientHeight / vr) * vr;
            let vrw = parseInt(ratio * vrh);
            img[i].setAttribute('width', vrw.toString());
            img[i].setAttribute('height', vrh.toString());
        }

        // Rich Text Editor
        const rte = document.querySelectorAll('[data-type=rte]');

        for (let i = 0; i < rte.length; i++) {
            CKEDITOR.replace(rte[i]);
        }
    });
})(document, CKEDITOR);
