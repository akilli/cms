/**
 * Global Listeners
 */
'use strict';

(function (document) {
    /**
     * Toggle
     */
    function toggle() {
        Array.prototype.forEach.call(document.querySelectorAll('span[data-action=toggle]'), function (item) {
            item.addEventListener('click', function () {
                const dt = this.getAttribute('data-target');
                const target = dt ? document.getElementById(dt) : null;

                if (this.hasAttribute('data-toggle')) {
                    this.removeAttribute('data-toggle');

                    if (!!target) {
                        target.setAttribute('data-toggle', '');
                    }
                } else {
                    this.setAttribute('data-toggle', '');

                    if (!!target) {
                        target.setAttribute('data-toggle', 'open');
                        target.scrollIntoView(true);
                    }
                }
            });
        });
    }

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
     * Event Listener
     */
    document.addEventListener('DOMContentLoaded', function () {
        toggle();
        datetime();
        multiCheckbox();
    });
})(document);
