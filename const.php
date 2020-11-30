<?php
declare(strict_types=1);

/**
 * Application constants
 */
define('APP', [
    'backend' => [
        'bool',
        'date',
        'datetime',
        'decimal',
        'int',
        'json',
        'multiint',
        'multitext',
        'serial',
        'text',
        'time',
        'varchar',
    ],
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
        'privilege' => [
            'name' => null,
            'delegate' => null,
            'auto' => false,
            'active' => true,
        ],
        'toolbar' => [
            'id' => null,
            'name' => null,
            'privilege' => null,
            'url' => null,
            'active' => true,
            'parent_id' => null,
            'sort' => 0,
            'position' => null,
            'level' => 0,
        ],
    ],
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
            'privilege' => null,
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
            'method' => null,
            'proto' => null,
            'post' => [],
            'url' => null,
        ],
    ],
    'date.backend' => 'Y-m-d',
    'date.frontend' => 'Y-m-d',
    'datetime.backend' => 'Y-m-d H:i:s',
    'datetime.frontend' => 'Y-m-d\TH:i',
    'html.tags' => '<a><abbr><article><audio><b><blockquote><br><cite><code><data><del><details><dfn><div><editor-block><em><figcaption><figure><h1><h2><h3><h4><h5><h6><hr><i><iframe><img><ins><kbd><li><mark><ol><p><pre><q><s><section><samp><small><strong><sub><summary><sup><table><tbody><td><tfoot><th><thead><time><tr><u><ul><var><video>',
    'html.void' => ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr', 'msg'],
    'image' => [
        'srcset' => [360, 640, 800, 960, 1120, 1280, 1440],
        'sizes' => null,
        'thumb' => 879,
    ],
    'image.ext' => ['jpg', 'png', 'webp'],
    'join' => [
        'cross' => 'CROSS',
        'full' => 'FULL',
        'inner' => 'INNER',
        'left' => 'LEFT',
        'natural' => 'NATURAL',
        'right' => 'RIGHT'
    ],
    'lang' => locale_get_primary_language(''),
    'locale' => ini_get('intl.default_locale'),
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
    'php.ext' => '.php',
    'response' => [
        'body' => '',
        'redirect' => null,
    ],
    'time.backend' => 'H:i:s',
    'time.frontend' => 'H:i',
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
