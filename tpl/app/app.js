<?php
namespace app;
/** @var callable $§ */
$cfg = ['i18' => cfg('i18n')];
$opt = cfg('opt');
$cfg['file'] = array_fill_keys($opt['audio'], 'audio') + array_fill_keys($opt['image'], 'img') + array_fill_keys($opt['video'], 'video');
ksort($cfg['file']);
?>
'use strict';

const app = {
    cfg: <?=json_encode($cfg);?>,
    i18n: function (key, ...args) {
        key = this.cfg.i18n[key] ? this.cfg.i18n[key] : key;
        args.map(function (i) {
            key = key.replace(/%s/, i);
        });

        return key;
    }
};
