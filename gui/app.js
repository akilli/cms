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
                mtime: 0,
                url: {},
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
            url: function (path) {
                return `/${path}`;
            },
            file: function (path) {
                return `${this.cfg.url.file}/${path}`;
            },
            gui: function (path) {
                return `${this.cfg.url.gui}/${this.cfg.mtime}/${path}`;
            },
            ext: function (path) {
                return `${this.cfg.url.ext}/${this.cfg.mtime}/${path}`;
            },
        };

        try {
            const data = JSON.parse(app.get(app.url('api/cfg')));

            Object.getOwnPropertyNames(app.cfg).forEach(name => {
                if (data[name] && typeof app.cfg[name] === typeof data[name]) {
                    app.cfg[name] = data[name];
                }
            });
        } catch (e) {
            console.log(e);
        }

        return app;
    })();
})(window);
