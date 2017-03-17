(function (document) {
    'use strict';

    document.addEventListener('DOMContentLoaded', function()
    {
        // Rich Text Editor
        const rte = document.querySelectorAll('[data-type=rte]');

        for (let i = 0; i < rte.length; i++) {
            CKEDITOR.replace(rte[i]);
        }

        // Toggle
        const toggles = document.querySelectorAll('[data-toggle]');

        for (let i = 0; i < toggles.length; i++) {
            let toggle = toggles[i];

            toggle.addEventListener('change', function () {
                const cb = document.querySelectorAll('input[type=checkbox][data-toggle-id=' + this.getAttribute('data-toggle') + ']');

                for (let i = 0; i < cb.length; i++) {
                    cb[i].checked = toggle.checked;
                }
            });
        }

        // Input password autocomplete fix
        const inputs = document.querySelectorAll('input[type=password]');

        for (let i = 0; i < inputs.length; i++) {
            let input = inputs[i];

            input.setAttribute('readonly', true);
            input.addEventListener('focus', function () {
                input.removeAttribute('readonly');
            })
        }
    });
})(document);
