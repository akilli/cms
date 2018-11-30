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
        multiCheckbox();
    });
})(document);
