/**
 * Slider
 */
'use strict';

(function (document, window) {
    window.addEventListener('load', function () {
        Array.prototype.forEach.call(document.getElementsByClassName('slider'), function (slider) {
            const items = slider.querySelector('.items');

            if (!items) {
                return;
            }

            const nav = slider.querySelector('.nav');
            const flickity = new Flickity(items, {
                autoPlay: true,
                imagesLoaded: true,
                fullscreen: slider.classList.contains('fullscreen'),
                pageDots: slider.classList.contains('dots'),
                prevNextButtons: slider.classList.contains('prevnext'),
                wrapAround: true
            });
            slider.addEventListener('mouseenter', function () {
                flickity.stopPlayer();
            });
            slider.addEventListener('mouseleave', function () {
                flickity.playPlayer();
            });

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
})(document, window);
