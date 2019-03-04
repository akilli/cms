<?php
declare(strict_types = 1);

/**
 * Constants used in application
 */
const APP = [
    'app' => [
        'action' => null,
        'area' => null,
        'entity' => null,
        'entity_id' => null,
        'gui' => null,
        'id' => null,
        'invalid' => false,
        'lang' => null,
        'page' => null,
        'parent_id' => null,
        'public' => false,
    ],
    'attr' => [
        'id' => null,
        'name' => null,
        'type' => null,
        'backend' => null,
        'frontend' => null,
        'filter' => null,
        'validator' => null,
        'viewer' => null,
        'virtual' => false,
        'auto' => false,
        'nullable' => false,
        'required' => false,
        'unique' => false,
        'multiple' => false,
        'ref' => null,
        'opt' => null,
        'opt.frontend' => null,
        'opt.filter' => null,
        'opt.validator' => null,
        'opt.viewer' => null,
        'min' => 0,
        'max' => 0,
        'accept' => [],
        'pattern' => null,
    ],
    'attr.date.backend' => 'Y-m-d',
    'attr.date.frontend' => 'Y-m-d',
    'attr.datetime.backend' => 'Y-m-d H:i:s',
    'attr.datetime.frontend' => 'Y-m-d\TH:i',
    'attr.time.backend' => 'H:i:s',
    'attr.time.frontend' => 'H:i',
    'backend' => ['bool', 'date', 'datetime', 'decimal', 'int', 'json', 'text', 'time', 'varchar'],
    'block' => [
        'id' => null,
        'call' => null,
        'tpl' => null,
        'cfg' => [],
    ],
    'crlf' => "\r\n",
    'curl' => [
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
    ],
    'entity' => [
        'id' => null,
        'name' => null,
        'db' => 'app',
        'type' => null,
        'parent_id' => null,
        'readonly' => false,
        'action' => [],
        'attr' => [],
    ],
    'entity.opt' => [
        'group' => [],
        'index' => 'id',
        'limit' => 0,
        'offset' => 0,
        'order' => [],
        'select' => [],
    ],
    'html.void' => ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'],
    'join' => [
        'full' => 'full',
        'inner' => 'inner',
        'left' => 'left',
        'right' => 'right'
    ],
    'layout' => [
        'id' => null,
        'type' => null,
        'call' => null,
        'tpl' => null,
        'active' => true,
        'priv' => null,
        'parent_id' => null,
        'sort' => 0,
        'cfg' => [],
    ],
    'layout.db' => 'layout-',
    'log' => 'php://stdout',
    'op' => [
        '=' => '=',
        '!=' => '!=',
        '>' => '>',
        '>=' => '>=',
        '<' => '<',
        '<=' => '<=',
        '~' => '~',
        '!~' => '!~',
        '^' => '^',
        '!^' => '!^',
        '$' => '$',
        '!$' => '!$',
    ],
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
    'pdo' => [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ],
    'priv' => [
        'name' => null,
        'priv' => null,
        'auto' => false,
        'active' => true,
    ],
    'redirect' => [301, 302, 307, 308],
    'toolbar' => [
        'id' => null,
        'name' => null,
        'action' => null,
        'url' => null,
        'active' => true,
        'parent_id' => null,
        'sort' => 0,
        'level' => 0,
    ],
    'upload' => ['error', 'name', 'size', 'tmp_name', 'type'],
    'url.file' => '/file/',
    'url.gui' => '/gui/',
    'version' => ['name', 'teaser', 'main', 'aside', 'status', 'timestamp']
];

/**
 * Include base source files
 */
foreach (glob(__DIR__ . '/src/*.php') as $file) {
    include_once $file;
}

/**
 * Include extension source files
 */
foreach (glob(app\path('ext.src', '*.php')) as $file) {
    include_once $file;
}
