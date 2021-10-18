import browser from './app/browser.js';
import confirmdelete from './app/confirmdelete.js';
import editor from './app/editor.js';
import invalid from './app/invalid.js';
import multicheckbox from './app/multicheckbox.js';
import menu from './app/menu.js';
import print from './app/print.js';
import privilege from './app/privilege.js';

print();
menu();
confirmdelete();
multicheckbox();
privilege();
invalid();
editor();
window.opener ? browser.win() : browser.open();
