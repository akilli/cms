'use strict';

(function (CKEDITOR) {
    const align = {left: 'left', center: 'center', right: 'right'};
    const types = {
        flac: 'audio',
        gif: 'img',
        jpg: 'img',
        mov: 'video',
        mp3: 'audio',
        mp4: 'video',
        oga: 'audio',
        ogg: 'audio',
        ogv: 'video',
        png: 'img',
        svg: 'img',
        wav: 'audio',
        weba: 'audio',
        webm: 'video',
        webp: 'img',
    };
    let tags = [];

    Object.getOwnPropertyNames(types).forEach(function (item) {
        if (!tags.includes(types[item])) {
            tags.push(types[item]);
        }
    });

    CKEDITOR.plugins.add('media', {
        requires: 'dialog,widget',
        icons: 'media',
        lang: 'de,en',
        init: function (editor) {
            editor.widgets.add('media', {
                button: editor.lang.media.title,
                dialog: 'media',
                template: '<figure class="media"><figcaption></figcaption></figure>',
                editables: {
                    caption: {
                        selector: 'figcaption',
                        allowedContent: 'strong em'
                    }
                },
                allowedContent: 'figure(!media, left, center, right); ' + tags.join(' ') + '[!src, alt, controls]; figcaption',
                requiredContent: 'figure(media); ' + tags.join(' ') + '[src]',
                defaults: {
                    align: '',
                    alt: '',
                    caption: false,
                    src: ''
                },
                upcast: function (element) {
                    return element.name == 'figure' && element.hasClass('media');
                },
                init: function () {
                    const widget = this;

                    // Media element
                    const media = this.element.findOne(tags.join(','));

                    if (media) {
                        ['src', 'alt'].forEach(function (name) {
                            if (media.hasAttribute(name)) {
                                widget.setData(name, media.getAttribute(name));
                            }
                        });
                    }

                    // Caption element
                    if (!!this.element.findOne('figcaption')) {
                        this.setData('caption', true);
                    }

                    // Container element
                    if (this.element.hasClass(align.left)) {
                        this.setData('align', 'left');
                    } else if (this.element.hasClass(align.center)) {
                        this.setData('align', 'center');
                    } else if (this.element.hasClass(align.right)) {
                        this.setData('align', 'right');
                    }
                },
                data: function () {
                    let ext;

                    if (!this.data.src || !(ext = this.data.src.split('.').pop()) || !types.hasOwnProperty(ext)) {
                        return;
                    }

                    let media = this.element.findOne(tags.join(','));
                    let caption = this.element.findOne('figcaption');

                    // Media element
                    if (!media || media.getName() !== types[ext]) {
                        if (media) {
                            media.remove();
                        }

                        media = new CKEDITOR.dom.element(types[ext]);

                        if (caption) {
                            media.insertBefore(caption);
                        } else {
                            this.element.append(media);
                        }
                    }

                    media.setAttribute('src', this.data.src);

                    if (types[ext] === 'img') {
                        media.setAttribute('alt', this.data.alt);
                    } else {
                        media.setAttribute('controls', true);
                    }

                    // Caption element
                    if (this.data.caption && !caption) {
                        this.element.append(new CKEDITOR.dom.element('figcaption'));
                    } else if (!this.data.caption && caption) {
                        caption.remove();
                    }

                    // Container element
                    this.element.removeClass(align.left);
                    this.element.removeClass(align.center);
                    this.element.removeClass(align.right);

                    if (this.data.align && align.hasOwnProperty(this.data.align)) {
                        this.element.addClass(align[this.data.align]);
                    }
                }
            });

            if (editor.contextMenu) {
                editor.addMenuGroup('media');
                editor.addMenuItem('media', {
                    label: editor.lang.media.menu,
                    command: 'media',
                    group: 'media'
                });
            }

            CKEDITOR.dialog.add('media', this.path + 'dialogs/media.js');
        }
    });
})(CKEDITOR);
