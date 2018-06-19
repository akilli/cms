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

            const nav = slider.querySelector('.slider-nav');
            const flickity = new Flickity(items, {
                autoPlay: true,
                imagesLoaded: true,
                fullscreen: slider.classList.contains('slider-fullscreen'),
                pageDots: slider.classList.contains('slider-dots'),
                prevNextButtons: slider.classList.contains('slider-prevnext'),
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

                Array.prototype.forEach.call(button, function (button) {
                    button.addEventListener('click', function (ev) {
                        flickity.select(Array.prototype.indexOf.call(button, ev.target));
                    });
                });

                flickity.on('select', function (current) {
                    Array.prototype.forEach.call(button, function (button, index) {
                        if (index === current) {
                            button.classList.add('is-selected');
                        } else {
                            button.classList.remove('is-selected');
                        }
                    });
                });
            }
        });
    });
})(document, window);
