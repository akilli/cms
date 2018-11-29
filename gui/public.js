/**
 * Public Listeners
 */
'use strict';

(function (document, Flickity) {
    /**
     * Link Types
     */
    function links() {
        Array.prototype.forEach.call(document.querySelectorAll('html[data-area=_public_] a[href]:not([role])'), function (item) {
            const href = item.getAttribute('href');
            const ext = href.match(/^https?:\/\//);

            if (ext) {
                item.setAttribute('target', '_blank');
            }

            if (!!item.querySelector('img')) {
                item.setAttribute('data-link', 'img');
            } else if (href.indexOf('/file/') === 0) {
                item.setAttribute('data-link', 'file');
            } else if (href.indexOf('/') === 0) {
                item.setAttribute('data-link', 'intern');
            } else if (href.indexOf('mailto:') === 0) {
                item.setAttribute('data-link', 'email');
            } else if (ext) {
                item.setAttribute('data-link', 'extern');
            }
        });
    }

    /**
     * Slider
     */
    function slider() {
        Array.prototype.forEach.call(document.getElementsByClassName('slider'), function (slider) {
            const items = slider.querySelector('.items');

            if (!items) {
                return;
            }

            const nav = slider.querySelector('.nav');
            const flickity = new Flickity(items, {
                autoPlay: slider.classList.contains('auto'),
                cellAlign: 'left',
                contain: true,
                imagesLoaded: true,
                fullscreen: slider.classList.contains('fullscreen'),
                pageDots: slider.classList.contains('dots'),
                percentPosition: false,
                prevNextButtons: slider.classList.contains('prevnext')
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
    }

    /**
     * Event Listener
     */
    document.addEventListener('DOMContentLoaded', function () {
        links();
        slider();
    });
})(document, Flickity);
