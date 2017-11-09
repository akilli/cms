CKEDITOR.dialog.add('mediaDialog', function (editor) {
    return {
        title: editor.lang.media.title,
        resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
        minWidth: 600,
        minHeight: 400,
        contents: [
            {
                id: 'tab-browser',
                label: editor.lang.media.browser,
                elements: [
                    {
                        type: 'html',
                        html: '<div id="mediaBrowser" style="white-space: normal;"></div>'
                    }
                ]
            }
        ],

        onShow: function () {
            var dialog = this;
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
                    const data = JSON.parse(this.responseText);
                    let mediaList = '';

                    for (let i = 0; i < data.length; i++) {
                        mediaList += '<img src="' + data[i].url + '" alt="' + data[i].name + '" style="max-width: 150px;max-height: 150px;vertical-align: middle;margin-right: 5px;" />';
                    }

                    document.querySelector('#mediaBrowser').innerHTML = mediaList;
                    var img = document.querySelectorAll('#mediaBrowser img');

                    for (var i = 0; i < img.length; i++) {
                        img[i].addEventListener('click', function () {
                            var img = editor.document.createElement('img');
                            img.setAttribute('src', this.getAttribute('src'));
                            img.setAttribute('alt', this.getAttribute('alt'));
                            editor.insertElement(img);
                            dialog.hide();
                        });
                    }
                }
            };
            xhr.open('GET', editor.config.mediaURL, true);
            xhr.send();
        }
    };
});
