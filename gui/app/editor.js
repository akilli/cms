import Editor from '../editor/src/Editor.js';
import configEditor from './cfg/editor.js';

/**
 * Editor
 *
 * @type {Function}
 */
export default function () {
    document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('textarea[data-type=editor]').forEach(item => Editor.create(item, configEditor)));
}
