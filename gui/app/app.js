const lang = document.documentElement.getAttribute('lang') || 'en';

/**
 * App
 */
export default {
    /**
     * Configuration
     *
     * @type {Object}
     * @readonly
     */
    cfg: {
        /**
         * Editor
         *
         * @type {Object}
         * @readonly
         */
        editor: {
            audiobrowser: '/file_audio/browser',
            blockapi: '/block/api',
            blockbrowser: '/block/browser',
            iframebrowser: '/file_iframe/browser',
            imagebrowser: '/file_image/browser',
            lang: lang,
            section: {
                'block-content': 'Content Block',
            },
            videobrowser: '/file_video/browser',
        },

        /**
         * Language
         *
         * @type {String}
         * @readonly
         */
        lang: lang,

        /**
         * Translations
         *
         * @type {Object}
         * @readonly
         */
        i18n: {
            'Please confirm delete operation': 'Bitte den Löschvorgang bestätigen',
        },
    },

    /**
     * Translates given string
     *
     * @param {String} key
     * @param {...String} args
     *
     * @returns {String}
     */
    i18n(key, ...args) {
        key = this.cfg.i18n[key] ? this.cfg.i18n[key] : key;

        for (let i = 0; i < args.length; i++) {
            key = key.replace(/%s/, args[i]);
        }

        return key;
    },
};
