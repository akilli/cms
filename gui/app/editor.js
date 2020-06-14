import Editor from '../editor/editor.js';
import lang from './lang.js';

/**
 * Editor configuration
 *
 * @type {Object.<String, Object.<String, Object>>}
 */
const config = {
    audio: {
        browser: '/file_audio/browser',
    },
    base: {
        lang: lang,
    },
    block: {
        api: '/block/api/{id}',
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
};

/**
 * Editor
 *
 * @type {Function}
 */
export default function () {
    document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('textarea[data-type=editor]').forEach(item => Editor.create(item, config)));
}
