/**
 * Global Listeners
 */
'use strict';

(function (window, document, Flickity) {
    /**
     * Fallback for input[type=datetime-local] to input[type=date] + input[type=time]
     */
    function datetime() {
        const inputs = document.querySelectorAll('input[type=datetime-local]');
        const d = document.createElement('input');
        const t = document.createElement('input');

        d.type = 'date';
        t.type = 'time';

        if (inputs.length <= 0 || inputs[0].type === 'datetime-local' || d.type !== 'date' || t.type !== 'time') {
            return;
        }

        Array.prototype.forEach.call(inputs, function (item) {
            const date = document.createElement('input');
            const time = document.createElement('input');
            const blur = function () {
                if (date.value && date.checkValidity() && time.value && time.checkValidity()) {
                    item.value = date.value + 'T' + time.value;
                } else {
                    item.value = '';
                }
            };
            const regex = /^(\d\d\d\d-(?:0[1-9]|1[0-2])-(?:0[1-9]|[12][0-9]|3[01]))T((?:00|[0-9]|1[0-9]|2[0-3]):(?:[0-9]|[0-5][0-9]))$/;
            const val = item.value.match(regex) || ['', '', ''];

            date.type = 'date';
            date.required = item.required;
            date.value = val[1];
            date.addEventListener('blur', blur);
            time.type = 'time';
            time.required = item.required;
            time.value = val[2];
            time.addEventListener('blur', blur);
            item.setAttribute('hidden', '');
            item.parentElement.insertBefore(date, item);
            item.parentElement.insertBefore(time, item);
        });
    }

    /**
     * Multi-checkbox required fix
     */
    function multiCheckbox () {
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
    }

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
        datetime();
        multiCheckbox();
        links();
    });

    window.addEventListener('load', function () {
        slider();
    });
})(window, document, Flickity);
