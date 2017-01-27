(function (document) {
    'use strict';

    document.addEventListener('DOMContentLoaded', function()
    {
        // Rich Text Editor
        var rte = document.querySelectorAll('[data-type=rte]');

        for (var i = 0; i < rte.length; ++i) {
            new RTE(rte[i]);
        }

        // Toggle
        var toggle = document.querySelectorAll('[data-toggle]');

        for (var i = 0; i < toggle.length; ++i) {
            toggle[i].addEventListener('change', function () {
                var cb = document.querySelectorAll('input[type=checkbox][data-toggle-id=' + this.getAttribute('data-toggle') + ']');

                for (var i = 0; i < cb.length; ++i) {
                    cb[i].click();
                }
            });
        }
    });
})(document);
