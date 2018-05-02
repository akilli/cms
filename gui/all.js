'use strict';

(function (document, window) {
    document.addEventListener('DOMContentLoaded', function () {
        // Multi-checkbox required fix
        const sel = 'input[type=checkbox][multiple]';

        Array.prototype.forEach.call(document.querySelectorAll(sel + '[required]'), function (item) {
            item.addEventListener('change', function () {
                const req = !!this.form.querySelector(sel + '[name="' + this.name + '"]:checked');

                Array.prototype.forEach.call(this.form.querySelectorAll(sel + '[name="' + this.name + '"]'), function (sib) {
                    if (req) {
                        sib.removeAttribute('required');
                    } else {
                        sib.setAttribute('required', 'required');
                    }
                });
            });
        });
    });

    window.addEventListener('load', function () {
        // Slider
        Array.prototype.forEach.call(document.getElementsByClassName('slider'), function (item) {
            const sliderItems = item.querySelector('.slider-items');

            if (!sliderItems) {
                return;
            }

            const sliderNav = item.querySelector('.slider-nav');
            const flickity = new Flickity(sliderItems, {
                autoPlay: true,
                imagesLoaded: true,
                pageDots: item.classList.contains('slider-dots'),
                prevNextButtons: item.classList.contains('slider-prevnext'),
                wrapAround: true
            });
            item.addEventListener('mouseenter', function () {
                flickity.stopPlayer();
            });
            item.addEventListener('mouseleave', function () {
                flickity.playPlayer();
            });

            if (!!sliderNav) {
                const sliderButton = sliderNav.getElementsByTagName('button');

                Array.prototype.forEach.call(sliderButton, function (button) {
                    button.addEventListener('click', function (ev) {
                        flickity.select(Array.prototype.indexOf.call(sliderButton, ev.target));
                    });
                });

                flickity.on('select', function (current) {
                    Array.prototype.forEach.call(sliderButton, function (button, index) {
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
