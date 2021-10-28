<?php
declare(strict_types=1);

return [
    'backend' => 'varchar',
    'frontend' => 'file',
    'filter' => 'text',
    'validator' => 'urlpath',
    'viewer' => 'video',
    'ignorable' => true,
    'uploadable' => true,
    'autoedit' => true,
    'autosearch' => true,
    'autoview' => true,
    'accept' => [
        'video/mp4',
        'video/ogg',
        'video/webm',
    ],
];
