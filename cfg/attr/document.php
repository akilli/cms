<?php
declare(strict_types=1);

return [
    'backend' => 'varchar',
    'frontend' => 'file',
    'filter' => 'text',
    'validator' => 'urlpath',
    'viewer' => 'url',
    'ignorable' => true,
    'uploadable' => true,
    'autoedit' => true,
    'autosearch' => true,
    'autoview' => true,
    'accept' => [
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
    ],
];
