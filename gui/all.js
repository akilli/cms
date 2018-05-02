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

        // Gallery dialog
        Array.prototype.forEach.call(document.querySelectorAll('.gallery .items > *'), function (item) {
            item.addEventListener('click', function (e) {
                if (!!document.getElementById('dialog')) {
                    document.getElementById('dialog').parentElement.removeChild(document.getElementById('dialog'));
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
                img.setAttribute('src', this.getAttribute('href'));
                dialog.appendChild(img);
                // Open dialog
                dialog.setAttribute('open', '');
                e.preventDefault();
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

        // Sticky navigation polyfill
        const nav = document.querySelector('#menu.sticky');

        if (!!nav && window.getComputedStyle(nav).getPropertyValue('position') !== 'sticky') {
            setTimeout(function() {
                const pos = nav.offsetTop;
                const width = window.getComputedStyle(nav.parentElement).getPropertyValue('width');

                window.addEventListener('scroll', function () {
                    if (window.pageYOffset >= pos) {
                        nav.setAttribute('data-sticky', '');
                        nav.style.width = width;
                    } else {
                        nav.removeAttribute('data-sticky');
                        nav.removeAttribute('style');
                    }
                });
            });
        }
    });
})(document, window);
