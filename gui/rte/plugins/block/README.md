# Block Widget

This widget let's you create and edit non-editable and optionally previewable placeholder blocks.

You can edit the placeholder block through a simple dialog where you can enter the ID of the block into a text input field. How you use the ID-field is completely up to you. Whatever makes sense for you or rather your application is fine.

Additionally, you can integrate a block browser similar to a file/media browser and select the block through that. Your block browser implementation can pass the *id* and optionally the *content* of the selected block. If the *content* is passed, the selected block will be previewable.

In order to make blocks previewable that are already in the initial content when you load the editor, you have to provide an URL to a block API that will return the *content* for a requested *id*.

## Block Browser

To enable the block browser option, you need the [browser](https://ckeditor.com/cke4/addon/browser) plugin, a browser implementation that uses the browser plugin and have to configure the URL to the browser implementation, p.e.

    config.blockBrowser = '/example/url/to/browser';

Your block browser implementation can currently send following keys with the message:

    {
        id: ..., // required, the ID of the block,
        content: '...', // optional, the HTML for the preview
    }

## Block API

To enable the block API option, you just have to configure an URL callback function that will receive the ID of the block and must return the final URL to the block API, p.e.

    config.blockApi = function (id) {
        return '/example/url/to/api/' + id ;
    };

This widget will then issue a `GET` request to the final URL. The block API must only return the HTML content for the preview if the block with the requested ID exists.

## Technical

When you save the editor content, i.e. on *downcast*, the resulting placeholder will be a *block*-element with an *id* attribute

    <block id=""/>

When you load your content into the editor, i.e. on *upcast*, the *block*-element will be tranformed to a *div*-element with a *data-block*-attribute

    <div data-block=""></div>

This widget will add some minimal CSS to the editor to make the placeholder somehow visible. You likely want add more styles to adjust the look to your needs, especially if you make use of the block browser and API options.

## Demo

https://akilli.github.io/rte/ck4

## Minimalistic browser example

https://github.com/akilli/rte/tree/master/browser
