/**
 * Application helper functions
 */
'use strict';

const app = {
    cfg: {
        i18n: {
            de: {
                'Page not found': 'Seite nicht gefunden',
                'Please confirm delete operation': 'Bitte den Löschvorgang bestätigen'
            }
        },
        lang: document.querySelector('html').getAttribute('lang'),
    },
    ajax: function (method, url) {
        if (!method || !url) {
            return null;
        }

        const xhr = new XMLHttpRequest();

        try {
            xhr.open(method, url, false);
            xhr.send();

            if (xhr.readyState === xhr.DONE && xhr.status >= 200 && xhr.status < 300) {
                return xhr.response;
            }
        } catch (e) {
            console.log(e);
        }

        return null;
    },
    i18n: function (key) {
        const data = this.cfg.i18n[this.cfg.lang] || {};

        return data[key] ? data[key] : key;
    }
};
