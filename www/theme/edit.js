'use strict';

(function (document, app, ClassicEditor) {
    document.addEventListener('DOMContentLoaded', () => {
        // Rich Text Editor
        const rte = document.querySelectorAll('textarea[data-type=rte]');

        for (let i = 0; i < rte.length; i++) {
            ClassicEditor.create(rte[i]);
        }
    });
})(document, app, ClassicEditor);
