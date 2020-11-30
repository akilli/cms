import browser from './app/browser.js';
import datetime from './polyfill/datetime.js';
import confirmdelete from './app/confirmdelete.js';
import invalid from './app/invalid.js';
import multicheckbox from './app/multicheckbox.js';
import privilege from './app/privilege.js';
import editor from './app/editor.js';

datetime();
confirmdelete();
multicheckbox();
privilege();
invalid();
editor();
window.opener ? browser.win() : browser.open();
