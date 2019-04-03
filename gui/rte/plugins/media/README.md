# Media Widget

This widget embeds image, video, audio and iframe elements wrapped within a figure and optionally with a caption.

The figure will get an appropriate CSS class reflecting the media type (*image*, *video*, *audio* or *iframe*) and, if alignment is set, one of *left* or *right*.

Currently this widget supports setting the attributes *width* and *height* for all media types and *alt* for images. The *controls* (audio and video) and *allowfullscreen* (iframe) are automatically set.

The resulting HTML for an image with a caption will be p.e.

    <figure class="image">
        <img src="/url/to/media" alt="Some Alternative" />
        <figcaption>An image with alternative text, but without width and height set</figcaption>
    </figure>

## Supported browser APIs

If you provide a browser implementation that uses one of the following browser APIs the _Browse server_ button will appear:

1. [browser](https://ckeditor.com/cke4/addon/browser)
2. [mediabrowser](https://ckeditor.com/cke4/addon/mediabrowser)
3. [filebrowser](https://ckeditor.com/cke4/addon/filebrowser)

**This widget itself does not provide any browser!**

The browser plugin (if installed) will be used as the preferred option, when the URL to your browser implementation is configured

    config.mediaBrowser = '/url/to/browser';

Otherwise the mediabrowser plugin (if installed and configured) will be used as the second option, or the filebrowser plugin (if installed) as the third option.

## Usage with browser and mediabrowser plugin

Your browser implementation can currently send following keys with the message:

    {
        src: '...', // required, URL to media
        type: '...', // optional, audio, iframe, image or video
        alt: '...', // optional, alternative text (only for images)
        width: '...', // optional
        height: '...' // optional
    }

## Note

If you need inline media elements in the resulting HTML, please stick with version [0.20](https://download.ckeditor.com/media/releases/media_0.20.zip) or use another plugin, because this is not supported anymore.

Inline media elements initially loaded into the editor content will automatically be wrapped inside a figure and stay there when you save the editor content, even if you omit the caption.

## Demo

https://akilli.github.io/rte/ck4

## Minimalistic browser example

https://github.com/akilli/rte/tree/master/browser
