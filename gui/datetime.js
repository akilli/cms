/**
 * Fallback for input[type=datetime-local] to input[type=date] + input[type=time]
 */
'use strict';

(function (document) {
    document.addEventListener('DOMContentLoaded', function () {
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

            date.type = 'date';
            date.required = item.required;
            date.addEventListener('blur', blur);
            time.type = 'time';
            time.required = item.required;
            time.addEventListener('blur', blur);
            item.setAttribute('hidden', '');
            item.parentElement.insertBefore(date, item);
            item.parentElement.insertBefore(time, item);
        });
    });
})(document);
