<?php
return [
    'audio' => [
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
    ],
    'bool' => [
        'backend' => 'bool',
        'frontend' => 'bool',
        'filter' => 'select',
        'viewer' => 'opt',
        'opt' => 'bool',
        'autoedit' => true,
        'autofilter' => true,
        'autoindex' => true,
    ],
    'date' => [
        'backend' => 'date',
        'frontend' => 'date',
        'validator' => 'date',
        'viewer' => 'date',
        'autoedit' => true,
        'autofilter' => true,
        'autoindex' => true,
    ],
    'datetime' => [
        'backend' => 'datetime',
        'frontend' => 'datetime',
        'filter' => 'date',
        'validator' => 'datetime',
        'viewer' => 'datetime',
        'autoedit' => true,
        'autofilter' => true,
        'autoindex' => true,
    ],
    'decimal' => [
        'backend' => 'decimal',
        'frontend' => 'decimal',
        'viewer' => 'raw',
        'autoedit' => true,
        'autoindex' => true,
    ],
    'document' => [
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
    ],
    'editor' => [
        'backend' => 'text',
        'frontend' => 'editor',
        'filter' => 'text',
        'validator' => 'editor',
        'viewer' => 'raw',
        'autoedit' => true,
        'autosearch' => true,
        'autoview' => true,
    ],
    'email' => [
        'backend' => 'varchar',
        'frontend' => 'email',
        'validator' => 'email',
        'viewer' => 'email',
        'autoedit' => true,
        'autosearch' => true,
        'autoindex' => true,
    ],
    'entity' => [
        'backend' => 'int',
        'frontend' => 'select',
        'validator' => 'entity',
        'viewer' => 'entity',
        'opt' => 'entity',
        'autoedit' => true,
        'autofilter' => true,
        'autoindex' => true,
    ],
    'entitychild' => [
        'backend' => 'varchar',
        'frontend' => 'select',
        'validator' => 'opt',
        'viewer' => 'opt',
        'opt' => 'entitychild',
        'autoedit' => true,
        'autofilter' => true,
        'autoindex' => true,
        'max' => 50,
    ],
    'file' => [
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
    ],
    'iframe' => [
        'backend' => 'varchar',
        'frontend' => 'url',
        'validator' => 'url',
        'viewer' => 'iframe',
        'autoedit' => true,
        'autosearch' => true,
        'autoview' => true,
    ],
    'image' => [
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
        'accept' => ['image/avif', 'image/gif', 'image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'],
    ],
    'int' => [
        'backend' => 'int',
        'frontend' => 'int',
        'viewer' => 'raw',
        'autoedit' => true,
        'autoindex' => true,
    ],
    'json' => [
        'backend' => 'json',
        'frontend' => 'json',
        'filter' => 'text',
        'viewer' => 'json',
        'autoedit' => true,
    ],
    'multientity' => [
        'backend' => 'multiint',
        'frontend' => 'select',
        'validator' => 'multientity',
        'viewer' => 'multientity',
        'opt' => 'entity',
        'autoedit' => true,
        'autofilter' => true,
    ],
    'multiint' => [
        'backend' => 'multiint',
        'frontend' => 'checkbox',
        'filter' => 'select',
        'validator' => 'opt',
        'viewer' => 'opt',
        'autoedit' => true,
        'autofilter' => true,
    ],
    'multitext' => [
        'backend' => 'multitext',
        'frontend' => 'checkbox',
        'filter' => 'select',
        'validator' => 'opt',
        'viewer' => 'opt',
        'autoedit' => true,
        'autofilter' => true,
    ],
    'password' => [
        'backend' => 'varchar',
        'frontend' => 'password',
        'ignorable' => true,
        'autoedit' => true,
        'autocomplete' => 'new-password',
    ],
    'position' => [
        'backend' => 'varchar',
        'frontend' => 'text',
        'validator' => 'text',
        'viewer' => 'position',
        'auto' => true,
        'autoedit' => true,
        'autosearch' => true,
        'autoindex' => true,
        'max' => 255,
    ],
    'radio' => [
        'backend' => 'varchar',
        'frontend' => 'radio',
        'filter' => 'select',
        'validator' => 'opt',
        'viewer' => 'opt',
        'autoedit' => true,
        'autofilter' => true,
        'autoindex' => true,
    ],
    'range' => [
        'backend' => 'int',
        'frontend' => 'range',
        'viewer' => 'raw',
        'autoedit' => true,
        'autoindex' => true,
    ],
    'select' => [
        'backend' => 'varchar',
        'frontend' => 'select',
        'validator' => 'opt',
        'viewer' => 'opt',
        'autoedit' => true,
        'autofilter' => true,
        'autoindex' => true,
    ],
    'serial' => [
        'backend' => 'serial',
        'frontend' => 'int',
        'viewer' => 'raw',
        'auto' => true,
    ],
    'tel' => [
        'backend' => 'varchar',
        'frontend' => 'tel',
        'validator' => 'text',
        'viewer' => 'tel',
        'autoedit' => true,
        'autosearch' => true,
        'autoindex' => true,
    ],
    'text' => [
        'backend' => 'varchar',
        'frontend' => 'text',
        'validator' => 'text',
        'viewer' => 'enc',
        'autoedit' => true,
        'autosearch' => true,
        'autoindex' => true,
        'autoview' => true,
    ],
    'textarea' => [
        'backend' => 'text',
        'frontend' => 'textarea',
        'filter' => 'text',
        'validator' => 'text',
        'viewer' => 'enc',
        'autoedit' => true,
        'autosearch' => true,
        'autoview' => true,
    ],
    'time' => [
        'backend' => 'time',
        'frontend' => 'time',
        'validator' => 'time',
        'viewer' => 'time',
        'autoedit' => true,
        'autoindex' => true,
    ],
    'uid' => [
        'backend' => 'varchar',
        'frontend' => 'text',
        'validator' => 'uid',
        'viewer' => 'enc',
        'autoedit' => true,
        'autosearch' => true,
    ],
    'url' => [
        'backend' => 'varchar',
        'frontend' => 'url',
        'validator' => 'url',
        'viewer' => 'url',
        'autoedit' => true,
        'autosearch' => true,
        'autoindex' => true,
        'max' => 255,
    ],
    'urlpath' => [
        'backend' => 'varchar',
        'frontend' => 'text',
        'validator' => 'urlpath',
        'viewer' => 'url',
        'autoedit' => true,
        'autosearch' => true,
        'autoindex' => true,
        'max' => 255,
    ],
    'video' => [
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
        'accept' => ['video/mp4', 'video/ogg', 'video/webm'],
    ],
];
