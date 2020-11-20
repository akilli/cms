import Editor from '../editor/editor.js';
import lang from './lang.js';

/**
 * Editor configuration
 *
 * @type {Object.<String, Object.<String, Object>>}
 */
const config = {
    audio: {
        browser: '/file_audio/admin',
    },
    base: {
        lang: lang,
    },
    block: {
        api: '/block/api/{id}',
        browser: '/block/admin',
        css: '/gui/base.css,/gui/all.css',
    },
    iframe: {
        browser: '/file_iframe/admin',
    },
    image: {
        browser: '/file_image/admin',
    },
    video: {
        browser: '/file_video/admin',
    },
};

/**
 * Editor
 *
 * @type {Function}
 */
export default function () {
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('textarea[data-type=editor]').forEach(item => Editor.create(item, config));
    });
}
