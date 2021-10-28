<?php
declare(strict_types=1);

return [
    'backend' => 'varchar',
    'frontend' => 'file',
    'filter' => 'text',
    'validator' => 'urlpath',
    'viewer' => 'image',
    'ignorable' => true,
    'uploadable' => true,
    'autoedit' => true,
    'autosearch' => true,
    'autoview' => true,
    'accept' => [
        'image/avif',
        'image/gif',
        'image/jpeg',
        'image/png',
        'image/svg+xml',
        'image/webp',
    ],
];
