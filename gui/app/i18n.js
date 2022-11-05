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
 * @type {function}
 *
 * @param {string} key
 * @param {...string} args
 *
 * @return {string}
 */
export default function (key, ...args) {
    key = cfg[lang]?.[key] || key;

    for (let i = 0; i < args.length; i++) {
        key = key.replace(/%s/, args[i]);
    }

    return key;
}
