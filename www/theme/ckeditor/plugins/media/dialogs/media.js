'use strict';

(function (document, CKEDITOR) {
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
                const xhr = new XMLHttpRequest();
                xhr.addEventListener('load', function () {
                    if (this.status !== 200) {
                        return;
                    }

                    const data = JSON.parse(this.responseText);
                    let mediaList = '';

                    for (let i = 0; i < data.length; i++) {
                        mediaList += '<img src="' + data[i].url + '" alt="' + data[i].name + '" />';
                    }

                    document.querySelector('#mediabrowser').innerHTML = mediaList;
                    let imgs = document.querySelectorAll('#mediabrowser img');

                    for (let i = 0; i < imgs.length; i++) {
                        imgs[i].addEventListener('click', function () {
                            editor.insertHtml('<figure class="media"><img src="' + this.getAttribute('src') + '" alt="' + this.getAttribute('alt') + '" /><figcaption></figcaption></figure>');
                            dialog.hide();
                        });
                    }
                });
                xhr.open('GET', editor.config.mediaURL, true);
                xhr.send();
            }
        };
    });
})(document, CKEDITOR);
