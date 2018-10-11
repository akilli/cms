<?php
namespace app;
/** @var callable $§ */
?>
'use strict';

const app = {
    cfg: {
        i18n: <?=json_encode(cfg('i18n'));?>
    },
    i18n: function (key) {
        key = this.cfg.i18n[key] ? this.cfg.i18n[key] : key;

        for (let i = 1; i < arguments.length; i++) {
            key = key.replace(/%s/, arguments[i]);
        }

        return key;
    }
};
