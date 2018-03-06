<?php
namespace app;
/** @var callable $§ */
$opt = cfg('opt');
$file = array_fill_keys($opt['audio'], 'audio') + array_fill_keys($opt['image'], 'img') + array_fill_keys($opt['video'], 'video');
?>
'use strict';

const app = {
    cfg: {
        file: <?=json_encode($file);?>,
        i18n: <?=json_encode(cfg('i18n'));?>
    },
    i18n: function (key, ...args) {
        key = this.cfg.i18n[key] ? this.cfg.i18n[key] : key;
        args.map(function (i) {
            key = key.replace(/%s/, i);
        });

        return key;
    }
};
