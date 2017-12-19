'use strict';

(function (document) {
    document.addEventListener('DOMContentLoaded', function () {
        // Multi-checkbox required fix
        document.querySelectorAll('form').forEach(function (form) {
            form.querySelectorAll('input[type=checkbox][required][multiple]').forEach(function (item) {
                item.addEventListener('change', function () {
                    let sel = 'input[type=checkbox][multiple][name="' + this.name + '"]';
                    let call;

                    if (form.querySelectorAll(sel + ':checked').length > 0) {
                        call = function (i) {
                            i.removeAttribute('required');
                        };
                    } else {
                        call = function (i) {
                            i.setAttribute('required', 'required');
                        };
                    }

                    form.querySelectorAll(sel).forEach(call);
                });
            });
        });
    });
})(document);
