/**
 * Gallery
 */
'use strict';

(function (document, Flickity) {
    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.getElementsByClassName('gallery'), function (gallery) {
            const content = gallery.querySelector('.content');

            if (!content) {
                return;
            }

            const flickity = new Flickity(content, {
                autoPlay: gallery.classList.contains('auto'),
                cellAlign: 'left',
                contain: true,
                imagesLoaded: true,
                fullscreen: gallery.classList.contains('fullscreen'),
                pageDots: false,
                percentPosition: false,
                prevNextButtons: gallery.classList.contains('prevnext')
            });
            const nav = gallery.querySelector('.nav');

            if (!!nav) {
                const button = nav.getElementsByTagName('button');

                Array.prototype.forEach.call(button, function (b) {
                    b.addEventListener('click', function (ev) {
                        flickity.select(Array.prototype.indexOf.call(button, ev.target));
                    });
                });

                flickity.on('select', function (current) {
                    Array.prototype.forEach.call(button, function (b, index) {
                        if (index === current) {
                            b.classList.add('is-selected');
                        } else {
                            b.classList.remove('is-selected');
                        }
                    });
                });
            }
        });
    });
})(document, Flickity);
