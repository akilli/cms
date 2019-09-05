import Editor from '../editor/src/Editor.js';
import editor from './cfg/editor.js';

/**
 * Editor
 *
 * @type {Function}
 */
export default function () {
    document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('textarea[data-type=editor]').forEach(item => Editor.create(item, editor)));
}
