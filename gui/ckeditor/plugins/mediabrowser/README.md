# Media Browser

This plugin provides a minimal media browser API as an alternative to the [filebrowser plugin](https://ckeditor.com/cke4/addon/filebrowser) and does not care about advanced features like file uploads, so stick with the [filebrowser plugin](https://ckeditor.com/cke4/addon/filebrowser) if you need those.

Unlike the [filebrowser plugin](https://ckeditor.com/cke4/addon/filebrowser) it does not use URL parameters to pass values between the editor and the browser windows, but uses the [window.postMessage()](https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage) function to communicate between both windows. So you can pass more than just the URL of the selected media from the media browser to the editor or even multiple media elements at once. Currently the only plugin that uses this API is the [Media Widget](https://ckeditor.com/cke4/addon/media).

## Plugin Integration

If you wanna use the API in your plugin, you just have to define a callback function `mediabrowser` in the _Browse server_ button configuration of your plugin's dialog, p.e.

    {
        id: 'browse',
        type: 'button',
        label: common.browseServer,
        hidden: true,
        mediabrowser: function (data) {
            if (!data.src) {
                return;
            }

            var dialog = this.getDialog();

            ['alt', 'src', 'type'].forEach(function (item) {
                if (!!data[item]) {
                    dialog.getContentElement('info', item).setValue(data[item]);
                }
            });
        }
    }

## Media Browser Integration

You can implement your media browser as you wish, the only two requirements are that you configure the URL to your media browser as `mediabrowserUrl` p.e.

    CKEDITOR.replace(document.getElementById('editor'), {
        ...
        mediabrowserUrl: '/url/to/mediabrowser',
        ...
    })

and that your media browser notifies the editor by posting a message p.e. like

    window.opener.postMessage({
        alt: 'Optional alternative text',
        src: '/url/to/media'
    }, origin);

## Example

You can see this plugin in action @ https://akilli.github.io/rte/ck4/ and find the source code of a minimalistic media browser example @ https://github.com/akilli/rte/tree/master/browser
