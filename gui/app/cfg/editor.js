import lang from './lang.js';

/**
 * Editor configuration
 *
 * @type {Object}
 */
export default {
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
}
