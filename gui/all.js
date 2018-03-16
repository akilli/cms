'use strict';

(function (document) {
    document.addEventListener('DOMContentLoaded', function () {
        // Multi-checkbox required fix
        const form = document.getElementsByTagName('form');

        for (let a = 0; a < form.length; a++) {
            let sel = 'input[type=checkbox][multiple]';
            let multi = form[a].querySelectorAll(sel + '[required]');

            for (let b = 0; b < multi.length; b++) {
                multi[b].addEventListener('change', function () {
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

        // Details
        const details = document.getElementsByTagName('details');

        if (details.length > 0 && typeof details[0].open !== 'boolean') {
            document.documentElement.setAttribute('data-shim-details', 'true');

            for (let a = 0; a < details.length; a++) {
                // Define open property
                Object.defineProperty(details[a], 'open', {
                    get: function() {
                        return this.hasAttribute('open');
                    },
                    set: function(state) {
                        if (state) {
                            this.setAttribute('open', '');
                        } else {
                            this.removeAttribute('open');
                        }
                    }
                });

                // Summary element
                let summary = details[a].firstChild;

                if (!summary || summary.tagName.toLowerCase() !== 'summary') {
                    summary = document.createElement('summary');
                    summary.innerText = 'Summary';
                    details[a].insertBefore(summary, details[a].firstChild);
                }

                summary.addEventListener('click', function() {
                    this.parentNode.open = !this.parentNode.hasAttribute('open');
                });
            }
        }
    });
})(document);
