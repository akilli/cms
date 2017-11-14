'use strict';

(function (document, CKEDITOR) {
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
                            html: '<div id="mediabrowser"></div>'
                        }
                    ]
                }
            ],

            onShow: function () {
                const dialog = this;
                get(editor.config.mediaURL)
                    .then(data => {
                        const media = JSON.parse(data);
                        const browser = document.querySelector('#mediabrowser');
                        browser.innerHTML = '';

                        for (let i = 0; i < media.length; i++) {
                            let item = document.createElement('img');
                            item.setAttribute('src', media[i].url);
                            item.setAttribute('alt', media[i].name);
                            let fig = document.createElement('figure');
                            fig.setAttribute('class', 'media');
                            fig.appendChild(item);
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
        };
    });
})(document, CKEDITOR);
