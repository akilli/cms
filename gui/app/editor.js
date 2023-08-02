import Editor from '../editor/editor.js';
import lang from './lang.js';

/**
 * Editor configuration
 *
 * @type {Object.<string, Object.<string, any>>}
 */
const config = {
    audio: {
        browser: '/audio:index',
    },
    base: {
        lang: lang,
        plugins: ['block'],
        pluginsDisabled: true,
    },
    iframe: {
        browser: '/iframe:index',
    },
    image: {
        browser: '/image:index',
    },
    video: {
        browser: '/video:index',
    },
};

/**
 * Editor
 *
 * @type {function}
 */
export default function () {
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('textarea[data-type=editor]').forEach(textarea => {
            const editor = Editor.create(textarea, config);

            // Replaces media elements with placeholders
            editor.root.addEventListener('gethtml', ev => {
                ev.detail.element.querySelectorAll(':is(audio, iframe, img, video)[id]').forEach(media => {
                    const file = document.createElement('app-file');
                    file.id = media.id;
                    media.parentElement.replaceChild(file, media);
                });
            });
        });
    });
}
