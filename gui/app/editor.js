import Editor from '../editor/src/Editor.js';
import app from './app.js';

/**
 * Editor
 */
export default function () {
    document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('textarea[data-type=editor]').forEach(item => Editor.create(item, app.cfg.editor)));
}
