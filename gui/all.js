'use strict';

(function (document) {
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
})(document);
