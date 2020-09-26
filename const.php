<?php
declare(strict_types = 1);

/**
 * Application constants
 */
define('APP', [
    'backend' => ['bool', 'date', 'datetime', 'decimal', 'int', 'int[]', 'json', 'text', 'text[]', 'time', 'varchar'],
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
            'autocomplete' => null,
            'pattern' => null,
            'accept' => [],
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
            'unique' => [],
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
    'container' => ['body', 'head', 'main'],
    'crlf' => "\r\n",
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
            'image' => [],
            'cfg' => [],
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
    'html.void' => ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr', 'msg'],
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
    'log' => 'php://stdout',
    'mtime' => max(filemtime('/app'), filemtime('/opt')),
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
        'src' => '/app/src',
        'tpl' => '/app/tpl',
        'ext.cfg' => '/opt/cfg',
        'ext.src' => '/opt/src',
        'ext.tpl' => '/opt/tpl',
        'file' => '/data/file',
        'tmp' => '/tmp',
    ],
    'pdo' => [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ],
    'response' => [
        'body' => '',
        'redirect' => null,
    ],
    'upload' => ['error', 'name', 'size', 'tmp_name', 'type'],
    'url' => [
        'ext' => '/ext',
        'file' => '/file',
        'gui' => '/gui',
    ],
    'viewer' => [
        'empty' => false,
        'h3' => false,
        'label' => false,
        'link' => null,
        'wrap' => false,
    ],
]);
