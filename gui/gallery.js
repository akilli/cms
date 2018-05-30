/**
 * Gallery dialog
 */
'use strict';

(function (document) {
    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('.gallery .items > *'), function (item) {
            item.addEventListener('click', function (e) {
                if (!!document.getElementById('dialog')) {
                    document.getElementById('dialog').parentElement.removeChild(document.getElementById('dialog'));
                }

                const tag = this.tagName.toLowerCase();
                let src;

                if (tag === 'img') {
                    src = this.getAttribute('src');
                } else if (tag === 'a' && this.hasAttribute('href')) {
                    src = this.getAttribute('href');
                } else {
                    src = !!(src = this.querySelector('img')) ? src.getAttribute('src') : null;
                }

                if (!src) {
                    return;
                }

                let current = this;
                const dialog = document.createElement('dialog');
                const img = document.createElement('img');
                const close = document.createElement('button');
                const prev = document.createElement('button');
                const next = document.createElement('button');
                const body = document.getElementsByTagName('body')[0];

                // Dialog
                dialog.id = 'dialog';
                dialog.addEventListener('click', function (e) {
                    if (e.target === this) {
                        dialog.parentElement.removeChild(this);
                    }
                });
                body.appendChild(dialog);
                // Close button
                close.setAttribute('data-act', 'close');
                close.innerText = 'x';
                close.addEventListener('click', function () {
                    dialog.parentElement.removeChild(dialog);
                });
                dialog.appendChild(close);
                // Prev button
                prev.setAttribute('data-act', 'prev');
                prev.innerText = '<';
                prev.addEventListener('click', function () {
                    const ref = current.previousElementSibling || current.parentElement.lastElementChild;
                    img.setAttribute('src', ref.getAttribute('href'));
                    current = ref;
                });
                dialog.appendChild(prev);
                // Next button
                next.setAttribute('data-act', 'next');
                next.innerText = '>';
                next.addEventListener('click', function () {
                    const ref = current.nextElementSibling || current.parentElement.firstElementChild;
                    img.setAttribute('src', ref.getAttribute('href'));
                    current = ref;
                });
                dialog.appendChild(next);
                // Image
                img.setAttribute('src', src);
                dialog.appendChild(img);
                // Open dialog
                dialog.setAttribute('open', '');
                e.preventDefault();
            });
        });
    });
})(document);
