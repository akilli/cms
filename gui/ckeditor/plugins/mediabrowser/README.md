# Media Browser

This plugin provides a minimal media browser API as an alternative to the [filebrowser add-on](https://ckeditor.com/cke4/addon/filebrowser). This API does not care about advanced features like file uploads, so stick with the [filebrowser add-on](https://ckeditor.com/cke4/addon/filebrowser) if you need those.

Unlike the [filebrowser add-on](https://ckeditor.com/cke4/addon/filebrowser) it does not use URL parameters to pass values between the editor and the browser windows, but uses the [window.postMessage()](https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage) function to communicate between both windows.

Depending on the media plugin you use or rather the `mediabrowser` property of the _Browse server_ button of its dialog, you can pass more than just the URL of the selected media from the media browser to the editor.

    {
        id: 'browse',
        type: 'button',
        label: common.browseServer,
        hidden: true,
        mediabrowser: {alt: 'info:alt', src: 'info:src'}
    }

Currently the only media plugin that uses this API is the [media add-on](https://ckeditor.com/cke4/addon/media).

You can implement your media browser as you wish, the only two requirements are that you configure the URL to your media browser as `mediabrowserUrl` p.e.

    CKEDITOR.replace(document.getElementById('editor'), {
        ...
        mediabrowserUrl: '/url/to/mediabrowser',
        ...
    })

and that your media browser notifies the editor by posting a message p.e. like

    // NOTE: window.opener.origin is only accessible on the same domain
    window.opener.postMessage({
        alt: 'Optional alternative text',
        src: '/url/to/media'
    }, window.opener.origin);

You can find a minimalistic media browser example @ https://github.com/akilli/rte/tree/master/browser
