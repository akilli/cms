<?php
declare(strict_types = 1);

const APP = [
    'attr' => [
        'id' => null,
        'name' => null,
        'type' => null,
        'backend' => null,
        'frontend' => null,
        'filter' => null,
        'viewer' => null,
        'virtual' => false,
        'auto' => false,
        'nullable' => false,
        'required' => false,
        'unique' => false,
        'multiple' => false,
        'ignorable' => false,
        'ref' => null,
        'opt' => null,
        'val' => null,
        'min' => 0,
        'max' => 0,
        'minlength' => 0,
        'maxlength' => 0,
        'pattern' => null,
        'cfg.backend' => null,
        'cfg.frontend' => null,
        'cfg.filter' => null,
        'cfg.viewer' => null,
        'html' => [],
    ],
    'backend' => ['bool', 'date', 'datetime', 'decimal', 'int', 'json', 'text', 'time', 'varchar'],
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
    'crlf' => "\r\n",
    'entity' => [
        'id' => null,
        'name' => null,
        'type' => null,
        'parent' => null,
        'mail' => false,
        'action' => [],
        'attr' => [],
    ],
    'entity.opt' => [
        'mode' => 'all',
        'index' => 'id',
        'select' => [],
        'order' => [],
        'limit' => 0,
        'offset' => 0,
    ],
    'file' => [
        'aac' => 'audio',
        'flac' => 'audio',
        'gif' => 'img',
        'jpeg' => 'img',
        'jpg' => 'img',
        'mp3' => 'audio',
        'mp4' => 'video',
        'oga' => 'audio',
        'ogg' => 'audio',
        'ogv' => 'video',
        'png' => 'img',
        'svg' => 'img',
        'wav' => 'audio',
        'weba' => 'audio',
        'webm' => 'video',
        'webp' => 'img',
    ],
    'join' => ['full' => 'full', 'inner' => 'inner', 'left' => 'left', 'right' => 'right'],
    'log' => 'php://stdout',
    'path' => [
        'cfg' => '/app/cfg',
        'file' => '/data/file',
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
        'auto' => false,
        'active' => true,
    ],
    'redirect' => [301, 302, 307, 308],
    'toolbar' => [
        'name' => null,
        'url' => null,
        'priv' => null,
        'parent' => null,
        'sort' => 0,
        'level' => 0,
    ],
    'upload' => ['error', 'name', 'size', 'tmp_name', 'type'],
    'url.gui' => '/gui/',
    'version' => ['name', 'teaser', 'main', 'aside', 'sidebar', 'status', 'date']
];
