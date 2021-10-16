<?php
declare(strict_types=1);

/**
 * Application constants
 */
define('APP', [
    'block' => [
        'id' => null,
        'type' => null,
        'call' => null,
        'tpl' => null,
        'tag' => null,
        'active' => true,
        'privilege' => null,
        'parent_id' => null,
        'sort' => 0,
        'image' => [],
        'cfg' => [],
    ],
    'cfg' => [
        'api' => [
            'size' => null,
            'one' => null,
            'all' => null,
            'save' => null,
            'delete' => null,
            'transaction' => null,
        ],
        'attr' => [
            'id' => null,
            'name' => null,
            'type' => null,
            'ref' => null,
            'backend' => null,
            'frontend' => null,
            'filter' => null,
            'validator' => null,
            'viewer' => null,
            'opt' => null,
            'auto' => false,
            'nullable' => false,
            'required' => false,
            'unique' => false,
            'ignorable' => false,
            'uploadable' => false,
            'autoedit' => false,
            'autofilter' => false,
            'autosearch' => false,
            'autoindex' => false,
            'autoview' => false,
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
            'tag' => null,
            'cfg' => [],
        ],
        'db' => [
            'id' => null,
            'dsn' => null,
            'user' => null,
            'password' => null,
        ],
        'entity' => [
            'id' => null,
            'name' => null,
            'api' => 'pdo',
            'db' => 'app',
            'parent_id' => null,
            'readonly' => false,
            'unique' => [],
            'action' => [],
            'attr' => [],
        ],
        'event' => [
            'id' => null,
            'call' => null,
            'sort' => 0,
        ],
        'privilege' => [
            'id' => null,
            'name' => null,
            'auto' => false,
            'use' => null,
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
            'path' => [],
        ],
    ],
    'crlf' => "\r\n",
    'data' => [
        'app' => [
            'id' => null,
            'entity_id' => null,
            'action' => null,
            'item_id' => null,
            'parent_id' => null,
            'valid' => false,
            'type' => 'html',
            'event' => [],
            'entity' => null,
            'item' => null,
        ],
        'request' => [
            'id' => null,
            'base' => null,
            'url' => null,
            'proto' => null,
            'host' => null,
            'method' => null,
            'get' => [],
            'post' => [],
            'file' => [],
        ],
    ],
    'date.backend' => 'yyyy-MM-dd',
    'date.frontend' => 'yyyy-MM-dd',
    'datetime.backend' => 'yyyy-MM-dd HH:mm:ss',
    'datetime.frontend' => "yyyy-MM-dd'T'HH:mm",
    'html.tags' => implode('', [
        '<a>',
        '<abbr>',
        '<article>',
        '<audio>',
        '<b>',
        '<blockquote>',
        '<br>',
        '<cite>',
        '<code>',
        '<data>',
        '<del>',
        '<details>',
        '<dfn>',
        '<div>',
        '<em>',
        '<figcaption>',
        '<figure>',
        '<h1>',
        '<h2>',
        '<h3>',
        '<h4>',
        '<h5>',
        '<h6>',
        '<hr>',
        '<i>',
        '<iframe>',
        '<img>',
        '<ins>',
        '<kbd>',
        '<li>',
        '<mark>',
        '<ol>',
        '<p>',
        '<pre>',
        '<q>',
        '<s>',
        '<section>',
        '<samp>',
        '<small>',
        '<strong>',
        '<sub>',
        '<summary>',
        '<sup>',
        '<table>',
        '<tbody>',
        '<td>',
        '<tfoot>',
        '<th>',
        '<thead>',
        '<time>',
        '<tr>',
        '<u>',
        '<ul>',
        '<var>',
        '<video>',
        '<app-block>',
    ]),
    'html.void' => [
        'area',
        'base',
        'br',
        'col',
        'embed',
        'hr',
        'img',
        'input',
        'link',
        'meta',
        'param',
        'source',
        'track',
        'wbr',
    ],
    'image' => [
        'image/avif' => [
            'create' => 'imagecreatefromavif',
            'output' => 'imageavif',
            'quality' => 75,
            'ext' => 'avif',
        ],
        'image/gif' => [
            'create' => 'imagecreatefromgif',
            'output' => 'imagegif',
            'quality' => null,
            'ext' => 'gif',
        ],
        'image/jpeg' => [
            'create' => 'imagecreatefromjpeg',
            'output' => 'imagejpeg',
            'quality' => 75,
            'ext' => 'jpg',
        ],
        'image/png' => [
            'create' => 'imagecreatefrompng',
            'output' => 'imagepng',
            'quality' => 3,
            'ext' => 'png',
        ],
        'image/webp' => [
            'create' => 'imagecreatefromwebp',
            'output' => 'imagewebp',
            'quality' => 75,
            'ext' => 'webp',
        ],
    ],
    'image.ext' => ['jpg', 'png', 'webp'],
    'image.responsive' => [
        'srcset' => [360, 640, 800, 960, 1120, 1280, 1440],
        'sizes' => null,
    ],
    'join' => [
        'cross' => 'CROSS',
        'full' => 'FULL',
        'inner' => 'INNER',
        'left' => 'LEFT',
        'natural' => 'NATURAL',
        'right' => 'RIGHT',
    ],
    'lang' => locale_get_primary_language(''),
    'locale' => ini_get('intl.default_locale'),
    'log' => 'php://stdout',
    'nav' => [
        'name' => null,
        'url' => null,
        'level' => 0,
    ],
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
    'pager' => [
        'name' => null,
        'url' => null,
        'current' => false,
        'class' => null,
    ],
    'path' => [
        'app.cfg' => '/app/cfg',
        'app.gui' => '/app/gui',
        'app.src' => '/app/src',
        'app.tpl' => '/app/tpl',
        'asset' => '/data',
        'ext' => '/opt',
        'ext.cfg' => '/opt/cfg',
        'ext.gui' => '/opt/gui',
        'ext.src' => '/opt/src',
        'ext.tpl' => '/opt/tpl',
        'tmp' => '/tmp',
    ],
    'pdo' => [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ],
    'response' => [
        'body' => '',
        'header' => [],
    ],
    'time.backend' => 'HH:mm:ss',
    'time.frontend' => 'HH:mm',
    'type' => [
        'html' => 'text/html; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
        'multipart' => 'multipart/mixed; charset=utf-8',
        'text' => 'text/plain; charset=utf-8',
    ],
    'upload' => ['error', 'full_path', 'name', 'size', 'tmp_name', 'type'],
    'url' => [
        'asset' => '/asset',
        'ext' => '/ext',
        'gui' => '/gui',
    ],
]);
