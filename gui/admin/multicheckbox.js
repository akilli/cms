/**
 * Multi-checkbox required fix
 */
'use strict';

(function (document) {
    document.addEventListener('DOMContentLoaded', function () {
        const sel = 'input[type=checkbox][multiple]';

        [].forEach.call(document.querySelectorAll(sel + '[required]'), function (item) {
            item.addEventListener('change', function () {
                const req = !!this.form.querySelector(sel + '[name="' + this.name + '"]:checked');

                [].forEach.call(this.form.querySelectorAll(sel + '[name="' + this.name + '"]'), function (sib) {
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
