'use strict';

(function (document, BalloonEditor) {
    document.addEventListener('DOMContentLoaded', () => {
        const rte = document.querySelectorAll('div[data-type=rte]');

        for (let i = 0; i < rte.length; i++) {
            BalloonEditor.create(rte[i]);
        }
    });
})(document, BalloonEditor);
