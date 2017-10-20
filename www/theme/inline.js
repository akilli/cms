'use strict';

(function (document, app, BalloonEditor) {
    document.addEventListener('DOMContentLoaded', () => {
        // Rich Text Editor
        const rte = document.querySelectorAll('div[data-type=rte]');

        for (let i = 0; i < rte.length; i++) {
            BalloonEditor.create(rte[i]);
        }
    });
})(document, app, BalloonEditor);
