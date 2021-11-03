import browser from './app/browser.js';
import confirmdelete from './app/confirmdelete.js';
import editor from './app/editor.js';
import privilege from './app/privilege.js';

confirmdelete();
privilege();
editor();
window.opener ? browser.win() : browser.open();
