import browser from './app/browser.js';
import datetime from './polyfill/datetime.js';
import confirmdelete from './app/confirmdelete.js';
import invalid from './app/invalid.js';
import multicheckbox from './app/multicheckbox.js';
import priv from './app/priv.js';
import editor from './app/editor.js';

datetime();
confirmdelete();
multicheckbox();
priv();
invalid();
editor();
window.opener ? browser.win() : browser.open();
