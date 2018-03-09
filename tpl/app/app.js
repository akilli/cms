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
    i18n: function (key, ...args) {
        key = this.cfg.i18n[key] ? this.cfg.i18n[key] : key;
        args.map(function (i) {
            key = key.replace(/%s/, i);
        });

        return key;
    }
};
