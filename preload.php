<?php
declare(strict_types = 1);

/**
 * Constants used in application
 */
define('APP', [
    'app' => [
        'action' => null,
        'area' => null,
        'entity' => null,
        'entity_id' => null,
        'gui' => null,
        'id' => null,
        'invalid' => false,
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
        'ignorable' => false,
        'uploadable' => false,
        'ref' => null,
        'opt' => null,
        'opt.frontend' => null,
        'opt.filter' => null,
        'opt.validator' => null,
        'opt.viewer' => null,
        'cfg.frontend' => null,
        'cfg.filter' => null,
        'cfg.validator' => null,
        'cfg.viewer' => null,
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
    'charset' => ini_get('default_charset'),
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
    'file' => ['error', 'name', 'size', 'tmp_name', 'type'],
    'file.thumb' => '.thumb.',
    'html.void' => ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'],
    'join' => [
        'cross' => 'CROSS',
        'full' => 'FULL',
        'inner' => 'INNER',
        'left' => 'LEFT',
        'natural' => 'NATURAL',
        'right' => 'RIGHT'
    ],
    'lang' => locale_get_primary_language(''),
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
    'locale' => ini_get('intl.default_locale'),
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
    'url.file' => '/file/',
    'url.gui' => '/gui/',
    'version' => ['name', 'content', 'aside', 'status', 'timestamp']
]);

/**
 * Include base and extension source files
 */
foreach (glob(__DIR__ . '/src/*.php') as $file) {
    include_once $file;
}

foreach (glob(app\path('ext.src', '*.php')) as $file) {
    include_once $file;
}

/**
 * Register functions
 */
set_error_handler('app\error');
set_exception_handler('app\exception');
register_shutdown_function('app\shutdown');
setlocale(LC_ALL, APP['locale']);
