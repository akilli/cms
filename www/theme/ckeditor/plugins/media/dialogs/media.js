'use strict';

(function (document, CKEDITOR) {
    function list(q, editor, dialog) {
        get(editor.config.mediaURL + (q ? '?q=' + q : ''))
            .then(data => {
                const media = JSON.parse(data);
                const browser = document.querySelector('#ck-media');
                browser.innerHTML = '';

                for (let i = 0; i < media.length; i++) {
                    let fig = document.createElement('figure');
                    fig.setAttribute('class', 'media');
                    fig.appendChild(element(media[i]));
                    fig.appendChild(document.createElement('figcaption'));
                    fig.addEventListener('click', () => {
                        editor.insertHtml(fig.outerHTML);
                        dialog.hide();
                    });
                    browser.appendChild(fig);
                }
            })
            .catch(error => {
                console.log(error);
            });
    }

    function get(url) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.onload = () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    resolve(xhr.response);
                } else {
                    reject(xhr.statusText);
                }
            };
            xhr.onerror = () => reject(xhr.statusText);
            xhr.send();
        });
    }

    function element(obj) {
        let item;

        if (obj.type === 'audio' || obj.type === 'video') {
            item = document.createElement(obj.type);
            item.setAttribute('src', obj.url);
            item.setAttribute('controls', true);
        } else {
            item = document.createElement('img');
            item.setAttribute('src', obj.url);
            item.setAttribute('alt', obj.name);
        }

        return item;
    }

    CKEDITOR.dialog.add('media', function (editor) {
        return {
            title: editor.lang.media.title,
            resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
            minWidth: 600,
            minHeight: 400,
            contents: [
                {
                    elements: [
                        {
                            type: 'html',
                            html: '<div id="ck-media-top"><input type="search" placeholder="' + editor.lang.media.filter + '" /></div><div id="ck-media"></div>'
                        }
                    ]
                }
            ],

            onShow: function () {
                const search = document.querySelector('#ck-media-top input[type=search]');
                list(search.value || '', editor, this);
                document.querySelector('#ck-media-top input[type=search]').addEventListener('keyup', () => {
                    list(search.value || '', editor, this);
                });
            }
        };
    });
})(document, CKEDITOR);
