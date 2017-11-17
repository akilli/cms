'use strict';

(function (CKEDITOR) {
    const align = {left: 'left', center: 'center', right: 'right'};
    const editables = {
        caption: {
            selector: 'figcaption',
            allowedContent: 'strong em'
        }
    };
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
                editables: editables,
                allowedContent: 'figure(!media, left, center, right); ' + tags.join(' ') + '[!src, alt, controls]; figcaption',
                requiredContent: 'figure(media); ' + tags.join(' ') + '[src]',
                defaults: {
                    align: '',
                    alt: '',
                    caption: false,
                    src: ''
                },
                upcast: function (element) {
                    let cond = function (el) {
                        return el.name === 'figure' && el.hasClass('media')
                    };

                    return cond(element) || tags.includes(element.name) && !element.getAscendant(cond);
                },
                init: function () {
                    const widget = this;
                    const wrapper = this.element.getName() === 'figure' && this.element.hasClass('media');

                    // Media element
                    const media = wrapper ? this.element.findOne(tags.join(',')) : this.element;

                    if (media) {
                        ['src', 'alt'].forEach(function (name) {
                            if (media.hasAttribute(name)) {
                                widget.setData(name, media.getAttribute(name));
                            }
                        });
                    }

                    // Caption element
                    if (wrapper && !!this.element.findOne('figcaption')) {
                        this.setData('caption', true);
                    }

                    // Widget element
                    if (this.element.hasClass(align.left)) {
                        this.setData('align', 'left');
                    } else if (this.element.hasClass(align.center)) {
                        this.setData('align', 'center');
                    } else if (this.element.hasClass(align.right)) {
                        this.setData('align', 'right');
                    }
                },
                data: function () {
                    const ext = this.data.src ? this.data.src.split('.').pop() : null;

                    if (!ext || !types.hasOwnProperty(ext)) {
                        return;
                    }

                    let caption = this.element.findOne('figcaption');
                    let media = this.element.findOne(tags.join(','));

                    if (media) {
                        media.remove();
                    }

                    if (this.data.caption) {
                        if (this.element.getName() !== 'figure') {
                            this.element.renameNode('figure');
                            this.element.addClass('media');
                            this.element.removeAttribute('src');
                            this.element.removeAttribute('alt');
                            this.element.removeAttribute('controls');
                        }

                        if (!caption) {
                            caption = new CKEDITOR.dom.element('figcaption');
                            this.element.append(caption);
                            this.initEditable('caption', editables.caption);
                        }

                        media = new CKEDITOR.dom.element(types[ext]);
                        this.element.append(media, true);
                    } else {
                        if (this.element.getName() !== types[ext]) {
                            this.element.renameNode(types[ext]);
                        }

                        if (caption) {
                            caption.remove();
                        }

                        this.element.removeClass('media');
                        media = this.element;
                    }

                    // Media element
                    media.setAttribute('src', this.data.src);

                    if (types[ext] === 'img') {
                        media.setAttribute('alt', this.data.alt);
                    } else {
                        media.setAttribute('controls', true);
                    }

                    // Widget element
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
