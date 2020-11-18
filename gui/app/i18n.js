import de from './i18n/de.js';
import lang from './lang.js';

/**
 * Translations
 *
 * @type {Object}
 */
const cfg = {
    de,
};

/**
 * Translates given string
 *
 * @type {Function}
 *
 * @param {String} key
 * @param {...String} args
 *
 * @return {String}
 */
export default function (key, ...args) {
    if (cfg[lang] && cfg[lang][key]) {
        key = cfg[lang][key];
    }

    for (let i = 0; i < args.length; i++) {
        key = key.replace(/%s/, args[i]);
    }

    return key;
}
