import lang from './lang.js';

/**
 * Editor configuration
 *
 * @type {Object}
 */
export default {
    audio: {
        browser: '/file_audio/browser',
    },
    base: {
        lang: lang,
    },
    block: {
        api: '/block/api',
        browser: '/block/browser',
        css: '/gui/base.css,/gui/all.css',
    },
    iframe: {
        browser: '/file_iframe/browser',
    },
    image: {
        browser: '/file_image/browser',
    },
    video: {
        browser: '/file_video/browser',
    },
}
