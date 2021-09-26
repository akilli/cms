import Editor from '../editor/editor.js';
import lang from './lang.js';

/**
 * Editor configuration
 *
 * @type {Object.<String, Object.<String, Object>>}
 */
const config = {
    audio: {
        browser: '/:audio/index',
    },
    base: {
        lang: lang,
    },
    block: {
        api: '/:block/api/{id}',
        browser: '/:block/index',
        css: '/gui/base.css,/gui/all.css',
    },
    iframe: {
        browser: '/:iframe/index',
    },
    image: {
        browser: '/:image/index',
    },
    video: {
        browser: '/:video/index',
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
