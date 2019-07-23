/**
 * App
 */
'use strict';

(window => {
    if (window.app) {
        return;
    }

    window.app = (() => {
        const app = {
            cfg: {
                i18n: {},
                lang: 'en',
                rte: {},
            },
            ajax: function (method, url, body) {
                if (!method || !url) {
                    return null;
                }

                const xhr = new XMLHttpRequest();

                try {
                    xhr.open(method, url, false);
                    xhr.send(body);

                    if (xhr.readyState === xhr.DONE && xhr.status >= 200 && xhr.status < 300) {
                        return xhr.responseText;
                    }
                } catch (e) {
                    console.log(e);
                }

                return null;
            },
            get: function (url) {
                return this.ajax('GET', url, null);
            },
            post: function (url, body) {
                return this.ajax('POST', url, body);
            },
            i18n: function (key) {
                key = this.cfg.i18n[key] ? this.cfg.i18n[key] : key;

                for (let i = 1; i < arguments.length; i++) {
                    key = key.replace(/%s/, arguments[i]);
                }

                return key;
            },
        };

        try {
            const cfg = JSON.parse(app.get('/api/cfg'));

            Object.getOwnPropertyNames(app.cfg).forEach(name => {
                if (cfg[name] && typeof app.cfg[name] === typeof cfg[name]) {
                    app.cfg[name] = cfg[name];
                }
            });
        } catch (e) {
            console.log(e);
        }

        return app;
    })();
})(window);
