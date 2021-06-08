/**
 * Fallback for input[type=datetime-local] to input[type=date] + input[type=time]
 */
export default function () {
    document.addEventListener('DOMContentLoaded', () => {
        const inputs = document.querySelectorAll('input[type=datetime-local]');
        const d = document.createElement('input');
        const t = document.createElement('input');

        d.type = 'date';
        t.type = 'time';

        if (inputs.length <= 0 || inputs[0].type === 'datetime-local' || d.type !== 'date' || t.type !== 'time') {
            return;
        }

        inputs.forEach(item => {
            const date = document.createElement('input');
            const time = document.createElement('input');
            const change = () => {
                if (date.value && date.checkValidity()) {
                    if (!time.value || !time.checkValidity()) {
                        time.value = '00:00';
                    }

                    item.value = `${date.value}T${time.value}`;
                } else {
                    date.value = '';
                    time.value = '';
                    item.value = '';
                }
            };
            const regex =
                /^([0-9]{4}-(?:0[1-9]|1[0-2])-(?:0[1-9]|[12][0-9]|3[01]))T((?:[01][0-9]|2[0-3]):(?:[0-5][0-9]))$/;
            const val = item.value.match(regex) || ['', '', ''];

            date.type = 'date';
            date.required = item.required;
            date.value = val[1];
            date.addEventListener('change', change);
            time.type = 'time';
            time.required = item.required;
            time.value = val[2];
            time.addEventListener('change', change);
            item.hidden = true;
            item.insertAdjacentElement('afterend', time);
            item.insertAdjacentElement('afterend', date);
        });
    });
}
