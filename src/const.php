<?php
declare(strict_types = 1);

const APP = [
    'account.guest' => 'account-guest',
    'account.user' => 'account-user',
    'all' => '_all_',
    'area.admin' => '_admin_',
    'area.public' => '_public_',
    'attr' => [
        'id' => null,
        'name' => null,
        'virtual' => false,
        'auto' => false,
        'type' => null,
        'backend' => null,
        'frontend' => null,
        'nullable' => false,
        'required' => false,
        'unique' => false,
        'multiple' => false,
        'searchable' => false,
        'ignorable' => false,
        'ent' => null,
        'opt' => null,
        'val' => null,
        'min' => 0,
        'max' => 0,
        'minlength' => 0,
        'maxlength' => 0,
        'filter' => null,
        'viewer' => null,
    ],
    'backend' => ['bool', 'date', 'datetime', 'decimal', 'int', 'json', 'text', 'time', 'varchar'],
    'backend.date' => 'Y-m-d',
    'backend.datetime' => 'Y-m-d H:i:s',
    'backend.time' => 'H:i:s',
    'block' => [
        'id' => null,
        'type' => null,
        'tpl' => null,
        'active' => true,
        'priv' => null,
        'parent' => null,
        'sort' => 0,
        'vars' => [],
    ],
    'crit' => [
        '=' => '=',
        '!=' => '!=',
        '>' => '>',
        '>=' => '>=',
        '<' => '>',
        '<=' => '<=',
        '~' => '~',
        '!~' => '!~',
        '~^' => '~^',
        '!~^' => '!~^',
        '~$' => '~$',
        '!~$' => '!~$',
    ],
    'ent' => [
        'id' => null,
        'name' => null,
        'type' => null,
        'parent' => null,
        'act' => [],
        'attr' => [],
    ],
    'ent.opt' => [
        'mode' => 'all',
        'index' => 'id',
        'order' => [],
        'limit' => 0,
        'offset' => 0,
    ],
    'file.audio' => ['aac', 'flac', 'mp3', 'oga', 'ogg', 'wav', 'weba'],
    'file.image' => ['gif', 'jpg', 'png', 'svg', 'webp'],
    'file.video' => ['mp4', 'ogv', 'webm'],
    'frontend.date' => 'Y-m-d',
    'frontend.datetime' => 'Y-m-d\TH:i',
    'frontend.time' => 'H:i',
    'log' => 'php://stdout',
    'path' => [
        'asset' => '/data/asset',
        'cfg' => '/app/cfg',
        'gui' => '/app/gui',
        'tpl' => '/app/tpl',
        'ext.cfg' => '/data/ext/cfg',
        'ext.gui' => '/data/ext/gui',
        'ext.src' => '/data/ext/src',
        'ext.tpl' => '/data/ext/tpl',
    ],
    'priv' => [
        'name' => null,
        'priv' => null,
        'active' => true,
        'assignable' => true,
    ],
    'toolbar' => [
        'name' => null,
        'url' => null,
        'priv' => null,
        'parent' => null,
        'sort' => 0,
        'level' => 0,
    ],
    'upload' => ['error', 'name', 'size', 'tmp_name', 'type'],
    'url.ext' => '/ext/',
    'url.gui' => '/gui/',
    'version' => [
        'name' => null,
        'teaser' => null,
        'main' => null,
        'aside' => null,
        'sidebar' => null,
        'status' => null,
        'date' => null,
    ]
];
