import de from './i18n/de.js';

/**
 * Language
 *
 * @type {String}
 */
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
            block: {
                api: '/block/api',
                browser: '/block/browser',
            },
            lang: lang,
            media: {
                audio: '/file_audio/browser',
                iframe: '/file_iframe/browser',
                image: '/file_image/browser',
                video: '/file_video/browser',
            },
            section: {
                'block-content': 'Content Block',
            },
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
            de,
        },
    },

    /**
     * Translates given string
     *
     * @param {String} key
     * @param {...String} args
     *
     * @return {String}
     */
    i18n(key, ...args) {
        if (this.cfg.i18n[lang] && this.cfg.i18n[lang][key]) {
            key = this.cfg.i18n[lang][key];
        }

        for (let i = 0; i < args.length; i++) {
            key = key.replace(/%s/, args[i]);
        }

        return key;
    },
};
