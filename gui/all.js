'use strict';

(function (document) {
    document.addEventListener('DOMContentLoaded', function () {
        // Multi-checkbox required fix
        const forms = document.querySelectorAll('form');

        for (let a = 0; a < forms.length; a++) {
            let sel = 'input[type=checkbox][multiple]';
            let multi = forms[a].querySelectorAll(sel + '[required]');

            for (let b = 0; b < multi.length; b++) {
                multi[b].addEventListener('change', function (ev) {
                    let req = !!this.form.querySelector(sel + '[name="' + this.name + '"]:checked');
                    let sib = this.form.querySelectorAll(sel + '[name="' + this.name + '"]');

                    for (let c = 0; c < sib.length; c++) {
                        if (req) {
                            sib[c].removeAttribute('required');
                        } else {
                            sib[c].setAttribute('required', 'required');
                        }
                    }
                });
            }
        }
    });
})(document);
