<?php
declare(strict_types=1);

return [
    'backend' => 'varchar',
    'frontend' => 'file',
    'filter' => 'text',
    'validator' => 'urlpath',
    'viewer' => 'audio',
    'ignorable' => true,
    'uploadable' => true,
    'autoedit' => true,
    'autosearch' => true,
    'autoview' => true,
    'accept' => [
        'audio/aac',
        'audio/flac',
        'audio/mp3',
        'audio/mpeg',
        'audio/mpeg3',
        'audio/ogg',
        'audio/wav',
        'audio/wave',
        'audio/webm',
        'audio/x-aac',
        'audio/x-flac',
        'audio/x-mp3',
        'audio/x-mpeg',
        'audio/x-mpeg3',
        'audio/x-pn-wav',
        'audio/x-wav',
    ],
];
