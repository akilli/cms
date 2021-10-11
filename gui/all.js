import browser from './app/browser.js';
import confirmdelete from './app/confirmdelete.js';
import editor from './app/editor.js';
import invalid from './app/invalid.js';
import multicheckbox from './app/multicheckbox.js';
import nav from './app/nav.js';
import print from './app/print.js';
import privilege from './app/privilege.js';

print();
nav();
confirmdelete();
multicheckbox();
privilege();
invalid();
editor();
window.opener ? browser.win() : browser.open();
