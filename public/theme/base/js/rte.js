'use strict';

function RTE(el)
{
    const tags =
    {
        'mode' : function (editor, sel)
        {
            editor.setAttribute('data-rte-mode', editor.getAttribute('data-rte-mode') === 'src' ? 'rte' : 'src');
        },
        'undo' : function (editor, sel)
        {
            cmd('undo', null);
        },
        'redo' : function (editor, sel)
        {
            cmd('redo', null);
        },
        'remove' : function (editor, sel)
        {
            cmd('removeformat', null);
        },
        'b' : function (editor, sel)
        {
            cmd('bold', null);
        },
        'i' : function (editor, sel)
        {
            cmd('italic', null);
        },
        'u' : function (editor, sel)
        {
            insertHtml('u', sel);
        },
        'small' : function (editor, sel)
        {
            insertHtml('small', sel);
        },
        's' : function (editor, sel)
        {
            insertHtml('s', sel);
        },
        'sup' : function (editor, sel)
        {
            cmd('superscript', null);
        },
        'sub' : function (editor, sel)
        {
            cmd('subscript', null);
        },
        'link' : function (editor, sel)
        {
            if (value = prompt('URL', 'http://')) {
                cmd('createlink', value);
            }
        },
        'unlink' : function (editor, sel)
        {
            cmd('unlink', null);
        },
        'img' : function (editor, sel)
        {
            if (value = prompt('URL', 'http://')) {
                cmd('insertimage', value);
            }
        },
        'h1' : function (editor, sel)
        {
            cmd('formatblock', '<h1>');
        },
        'h2' : function (editor, sel)
        {
            cmd('formatblock', '<h2>');
        },
        'h3' : function (editor, sel)
        {
            cmd('formatblock', '<h3>');
        },
        'h4' : function (editor, sel)
        {
            cmd('formatblock', '<h4>');
        },
        'h5' : function (editor, sel)
        {
            cmd('formatblock', '<h5>');
        },
        'h6' : function (editor, sel)
        {
            cmd('formatblock', '<h6>');
        },
        'p' : function (editor, sel)
        {
            cmd('insertparagraph', null);
        },
        'blockquote' : function (editor, sel)
        {
            cmd('formatblock', '<blockquote>');
        },
        'ol' : function (editor, sel)
        {
            cmd('insertorderedlist', null);
        },
        'ul' : function (editor, sel)
        {
            cmd('insertunorderedlist', null);
        }
    };

    const allowed = ['b', 'i', 'u', 'strong', 'em', 'small', 's', 'sup', 'sub', 'a', 'img', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'blockquote', 'ol', 'ul', 'li', 'br'];

    function init(el)
    {
        let editor = el;

        if (el.nodeName.toLowerCase() === 'textarea') {
            editor = document.createElement('div');
            editor.setAttribute('contenteditable', true);
            editor.setAttribute('data-rte-mode', 'rte');
            editor.innerHTML = decode(el.innerHTML);
            el.parentNode.appendChild(editor);
            el.setAttribute('hidden', true);

            const f =  el.getAttribute('form'),
                form = f ? document.getElementById(f) : el.closest('form');

            form.addEventListener('submit', function()
            {
                el.innerHTML = trim(strip(decode(editor.innerHTML)));
            });

            toolbar(editor);
        } else {
            editor.addEventListener('focus', function()
            {
                toolbar(editor);
            });
        }

        editor.addEventListener('change', function()
        {
            let html = strip(decode(editor.innerHTML));
            editor.innerHTML = editor.getAttribute('data-rte-mode') === 'src' ? encode(html) : html;
        });
    }

    function toolbar(editor)
    {
        if (editor._initialized) {
            return;
        }

        const toolbar = document.createElement('div');
        toolbar.setAttribute('data-rte-toolbar', true);

        for (let key in tags) {
            if (!tags.hasOwnProperty(key)) {
                continue;
            }

            const button = document.createElement('button');
            button.setAttribute('type', 'button');
            button.setAttribute('data-rte-cmd', key);
            button.textContent = key;
            button.addEventListener('click', function()
            {
                const sel = window.getSelection(),
                    callback = tags[this.getAttribute('data-rte-cmd')] || null;

                // Selection outside element or invalid key
                if (!sel.containsNode(editor, true) || !callback) {
                    return false;
                }

                // Callback
                callback(editor, sel);
                editor.dispatchEvent(new Event('change'));

                return false;
            });

            toolbar.appendChild(button);
        }

        editor.parentNode.insertBefore(toolbar, editor);
        editor._initialized = true;
    }

    function cmd(command, value)
    {
        try {
            document.execCommand(command, false, value);
        } catch (e) {
            console.log(e);
        }
    }

    function insertHtml(tag, sel)
    {
        const html = sel.toString();

        if (tag && html.length > 0) {
            cmd('insertHTML', '<' + tag + '>' + html + '</' + tag + '>');
        }
    }

    function trim(html)
    {
        return html ? html.trim().replace(/\s/g, ' ').replace(/^((<|&lt;)br\s*\/*(>|&gt;))+/gi, ' ').replace(/((<|&lt;)br\s*\/*(>|&gt;))+$/gi, ' ').trim() : '';
    }

    function encode(html)
    {
        return html ? html.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;') : '';
    }

    function decode(html)
    {
        return html ? html.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&#039;/g, "'") : '';
    }

    function strip(html)
    {
        if (!html) {
            return '';
        }

        return html.replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/gi, function($0, $1)
        {
            return allowed.indexOf($1.toLowerCase()) > -1 ? $0 : '';
        });
    }

    init(el);
}
