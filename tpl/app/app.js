<?php
namespace app;
/** @var callable $§ */
?>
'use strict';

const app = {
    cfg: {
        file: <?=$§('file');?>,
        i18n: <?=$§('i18n');?>
    },
    i18n: function (key) {
        key = this.cfg.i18n[key] ? this.cfg.i18n[key] : key;

        for (let i = 1; i < arguments.length; i++) {
            key = key.replace(/%s/, arguments[i]);
        }

        return key;
    },
    param: function (name) {
        const match = window.location.search.match(new RegExp('(?:[\?&]|&)' + name + '=([^&]+)', 'i'));

        return match && match.length > 1 ? match[1] : null;
    }
};
