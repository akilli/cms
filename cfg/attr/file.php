<?php
declare(strict_types=1);

return [
    'backend' => 'varchar',
    'frontend' => 'file',
    'filter' => 'text',
    'validator' => 'urlpath',
    'viewer' => 'file',
    'ignorable' => true,
    'uploadable' => true,
    'autoedit' => true,
    'autosearch' => true,
    'autoview' => true,
    'accept' => [
        // audio
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
        // document
        'application/msword',
        'application/pdf',
        'application/vnd.ms-excel',
        'application/vnd.ms-excel.sheet.macroEnabled.12',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/zip',
        'text/csv',
        // iframe
        'text/html',
        // image
        'image/avif',
        'image/gif',
        'image/jpeg',
        'image/png',
        'image/svg+xml',
        'image/webp',
        // video
        'video/mp4',
        'video/ogg',
        'video/webm',
    ],
];
