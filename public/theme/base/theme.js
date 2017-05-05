(function (document, CKEDITOR) {
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
        const pwdInput = document.querySelectorAll('input[type=password]');

        for (let i = 0; i < pwdInput.length; i++) {
            pwdInput[i].setAttribute('readonly', true);
            pwdInput[i].addEventListener('focus', function () {
                this.removeAttribute('readonly');
            })
        }

        // Delete buttons
        const delInput = document.querySelectorAll('input[formaction$="/delete"]');

        for (let i = 0; i < delInput.length; i++) {
            delInput[i].addEventListener('click', function (event) {
                if (!confirm(app._('Please confirm delete operation'))) {
                    event.preventDefault();
                }
            })
        }
    });
})(document, CKEDITOR);
