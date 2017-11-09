'use strict';

(function (document, ClassicEditor) {
    document.addEventListener('DOMContentLoaded', () => {
        const rte = document.querySelectorAll('textarea[data-type=rte]');

        for (let i = 0; i < rte.length; i++) {
            ClassicEditor.create(rte[i]);
        }
    });
})(document, ClassicEditor);
