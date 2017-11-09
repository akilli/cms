'use strict';

(function (document, CKEDITOR) {
    document.addEventListener('DOMContentLoaded', () => {
        const rte = document.querySelectorAll('[data-type=rte]');

        for (let i = 0; i < rte.length; i++) {
            if (rte[i].tagName.toLowerCase() === 'textarea') {
                CKEDITOR.replace(rte[i]);
            } else {
                rte[i].setAttribute('contenteditable', true);
                CKEDITOR.inline(rte[i]);
            }
        }
    });
})(document, CKEDITOR);
