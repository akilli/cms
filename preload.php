<?php
declare(strict_types = 1);

/**
 * Application constants
 */
define('APP', [
    'backend' => ['bool', 'date', 'datetime', 'decimal', 'int', 'json', 'text', 'time', 'varchar'],
    'cfg' => [
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
            'cfg.backend' => null,
            'cfg.frontend' => null,
            'cfg.filter' => null,
            'cfg.validator' => null,
            'cfg.viewer' => null,
            'min' => 0,
            'max' => 0,
            'accept' => [],
            'pattern' => null,
        ],
        'block' => [
            'id' => null,
            'call' => null,
            'tpl' => null,
            'cfg' => [],
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
        'priv' => [
            'name' => null,
            'priv' => null,
            'auto' => false,
            'active' => true,
        ],
        'toolbar' => [
            'id' => null,
            'name' => null,
            'action' => null,
            'url' => null,
            'active' => true,
            'parent_id' => null,
            'sort' => 0,
            'pos' => null,
            'level' => 0,
        ],
    ],
    'charset' => ini_get('default_charset'),
    'crlf' => "\r\n",
    'curl' => [
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
    ],
    'data' => [
        'app' => [
            'action' => null,
            'area' => null,
            'entity' => null,
            'entity_id' => null,
            'id' => null,
            'invalid' => false,
            'page' => null,
            'parent_id' => null,
            'public' => false,
        ],
        'request' => [
            'base' => null,
            'file' => [],
            'full' => null,
            'get' => [],
            'host' => null,
            'proto' => null,
            'post' => [],
            'url' => null,
        ],
    ],
    'entity.opt' => [
        'group' => [],
        'index' => 'id',
        'limit' => 0,
        'offset' => 0,
        'order' => [],
        'select' => [],
    ],
    'html.void' => ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr', 'block', 'msg'],
    'image' => [
        'srcset' => [360, 640, 800, 960, 1120, 1280, 1440],
        'sizes' => null,
        'thumb' => 879,
    ],
    'image.ext' => ['jpg', 'png', 'webp'],
    'image.threshold' => 100,
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
        'image' => [],
        'cfg' => [],
    ],
    'locale' => ini_get('intl.default_locale'),
    'log' => 'php://stdout',
    'mtime' => max(filemtime('/app'), is_dir('/data/ext') ? filemtime('/data/ext') : 0),
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
        'ext.cfg' => '/data/ext/cfg',
        'ext.gui' => '/data/ext/gui',
        'ext.src' => '/data/ext/src',
        'ext.tpl' => '/data/ext/tpl',
        'file' => '/data/file',
        'gui' => '/app/gui',
        'src' => '/app/src',
        'tmp' => '/tmp',
        'tpl' => '/app/tpl',
    ],
    'pdo' => [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ],
    'thumb' => '.thumb',
    'upload' => ['error', 'name', 'size', 'tmp_name', 'type'],
    'url' => [
        'file' => '/file/',
        'gui' => '/gui/',
    ],
    'version' => ['name', 'title', 'content', 'aside', 'account_id', 'status', 'timestamp'],
]);

/**
 * Include base and extension source files
 */
foreach ([APP['path']['src'], APP['path']['ext.src']] as $path) {
    foreach (glob($path . '/*.php') as $file) {
        include_once $file;
    }
}

/**
 * Register functions
 */
set_error_handler('app\error');
set_exception_handler('app\exception');
register_shutdown_function('app\shutdown');
setlocale(LC_ALL, APP['locale']);

/**
 * Configuration
 *
 * @todo Use opcache.preload with PHP 7.4 and only use `define('CFG', cfg\preload());` instead of the following lines
 *
 * @see https://wiki.php.net/rfc/preload
 */
if (!file_exists('/tmp/cfg.php')) {
    file\save('/tmp/cfg.php', cfg\preload());
}

define('CFG', file\load('/tmp/cfg.php'));
