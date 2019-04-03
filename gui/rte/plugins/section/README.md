# Section Widget

This widget offers the possibility to add *section* elements into the editor and make them optionally distinguishable by configurable CSS classes, so you can apply different styles on them.

All sections will have three editables: heading, media element and content. But all of them are optional. If all editables are filled with content and CSS classes are configured, the resulting HTML will be

    <section class="...">
        <h2>...</h2>
        <figure class="...">...</figure>
        <div class="content">...</div>
    </section>

The optional configuration expects an object with one or several CSS classes as the properties and the corresponding labels visible in the section dialog as the values, p.e.

    config.section: {
        'block-content': 'Content Block',
        'block-info': 'Info Block',
        ...
    };

## Technical

This widget will always `upcast` all *section* elements, unless an element was already upcasted by another widget. To give other widgets the chance to do so, the value for `upcastPriority` (defaults to `10`) is set to `20`, so that the other widgets' `upcast` methods are called first.

## Demo

https://akilli.github.io/rte/ck4
