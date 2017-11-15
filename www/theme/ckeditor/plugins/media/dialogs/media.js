'use strict';

(function (document, CKEDITOR) {
    CKEDITOR.dialog.add('media', function (editor) {
        const lang = editor.lang.media;
        const common = editor.lang.common;

        return {
            title: lang.title,
            resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
            minWidth: 250,
            minHeight: 100,
            contents: [
                {
                    id: 'info',
                    label: lang.info,
                    elements: [
                        {

                            type: 'hbox',
                            children: [
                                {
                                    id: 'src',
                                    type: 'text',
                                    label: common.url,
                                    setup: function (widget) {
                                        this.setValue(widget.data.src);
                                    },
                                    commit: function (widget) {
                                        widget.setData('src', this.getValue());
                                    },
                                    validate: CKEDITOR.dialog.validate.notEmpty(lang.urlMissing)
                                },
                                {
                                    id: 'browse',
                                    type: 'button',
                                    label: common.browseServer,
                                    hidden: true,
                                    filebrowser: 'info:src'
                                }
                            ]
                        },
                        {
                            id: 'align',
                            type: 'radio',
                            label: common.align,
                            items: [
                                [common.alignNone, ''],
                                [common.alignLeft, 'left'],
                                [common.alignRight, 'right'],
                                [common.alignCenter, 'center']
                            ],
                            setup: function (widget) {
                                this.setValue(widget.data.align);
                            },
                            commit: function (widget) {
                                widget.setData('align', this.getValue());
                            }
                        },
                        {
                            id: 'alt',
                            type: 'text',
                            label: lang.alt,
                            setup: function (widget) {
                                this.setValue(widget.data.alt);
                            },
                            commit: function (widget) {
                                widget.setData('alt', this.getValue());
                            }
                        },
                        {
                            id: 'caption',
                            type: 'checkbox',
                            label: lang.caption,
                            requiredContent: 'figcaption',
                            setup: function (widget) {
                                this.setValue(widget.data.caption);
                            },
                            commit: function (widget) {
                                widget.setData('caption', this.getValue());
                            }
                        }
                    ]
                }
            ],

            onShow: function () {
                const widget = this.widget;
                console.log(widget);
            }
        };
    });
})(document, CKEDITOR);
