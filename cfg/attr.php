<?php
return [
    'audio' => [
        'backend' => 'varchar',
        'frontend' => 'file',
        'filter' => 'text',
        'validator' => 'file',
        'viewer' => 'audio',
        'ignorable' => true,
        'uploadable' => true,
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
    ],
    'date' => [
        'backend' => 'date',
        'frontend' => 'date',
        'validator' => 'date',
        'viewer' => 'date',
    ],
    'datetime' => [
        'backend' => 'datetime',
        'frontend' => 'datetime',
        'filter' => 'date',
        'validator' => 'datetime',
        'viewer' => 'datetime',
    ],
    'decimal' => [
        'backend' => 'decimal',
        'frontend' => 'decimal',
    ],
    'document' => [
        'backend' => 'varchar',
        'frontend' => 'file',
        'filter' => 'text',
        'validator' => 'file',
        'viewer' => 'file',
        'ignorable' => true,
        'uploadable' => true,
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
        'frontend' => 'textarea',
        'filter' => 'text',
        'validator' => 'editor',
        'viewer' => 'editor',
    ],
    'email' => [
        'backend' => 'varchar',
        'frontend' => 'email',
        'validator' => 'email',
        'viewer' => 'email',
    ],
    'entity' => [
        'backend' => 'int',
        'frontend' => 'select',
        'validator' => 'entity',
        'viewer' => 'entity',
        'opt' => 'entity',
    ],
    'entitychild' => [
        'backend' => 'varchar',
        'frontend' => 'select',
        'validator' => 'opt',
        'viewer' => 'opt',
        'opt' => 'entitychild',
        'max' => 50,
    ],
    'entityfile' => [
        'backend' => 'int',
        'frontend' => 'browser',
        'filter' => 'select',
        'validator' => 'entity',
        'viewer' => 'file',
        'opt' => 'entity',
    ],
    'file' => [
        'backend' => 'varchar',
        'frontend' => 'file',
        'filter' => 'text',
        'validator' => 'file',
        'viewer' => 'file',
        'ignorable' => true,
        'uploadable' => true,
    ],
    'iframe' => [
        'backend' => 'varchar',
        'frontend' => 'url',
        'validator' => 'url',
        'viewer' => 'iframe',
    ],
    'image' => [
        'backend' => 'varchar',
        'frontend' => 'file',
        'filter' => 'text',
        'validator' => 'file',
        'viewer' => 'image',
        'ignorable' => true,
        'uploadable' => true,
        'accept' => ['image/gif', 'image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'],
    ],
    'int' => [
        'backend' => 'int',
        'frontend' => 'int',
    ],
    'json' => [
        'backend' => 'json',
        'frontend' => 'json',
        'filter' => 'text',
        'viewer' => 'json',
    ],
    'multientity' => [
        'backend' => 'multiint',
        'frontend' => 'select',
        'validator' => 'multientity',
        'viewer' => 'multientity',
        'opt' => 'entity',
    ],
    'multiint' => [
        'backend' => 'multiint',
        'frontend' => 'checkbox',
        'filter' => 'select',
        'validator' => 'opt',
        'viewer' => 'opt',
    ],
    'multitext' => [
        'backend' => 'multitext',
        'frontend' => 'checkbox',
        'filter' => 'select',
        'validator' => 'opt',
        'viewer' => 'opt',
    ],
    'password' => [
        'backend' => 'varchar',
        'frontend' => 'password',
        'ignorable' => true,
        'autocomplete' => 'new-password',
    ],
    'position' => [
        'backend' => 'varchar',
        'frontend' => 'text',
        'validator' => 'text',
        'viewer' => 'position',
        'auto' => true,
        'max' => 255,
    ],
    'radio' => [
        'backend' => 'varchar',
        'frontend' => 'radio',
        'filter' => 'select',
        'validator' => 'opt',
        'viewer' => 'opt',
    ],
    'range' => [
        'backend' => 'int',
        'frontend' => 'range',
    ],
    'select' => [
        'backend' => 'varchar',
        'frontend' => 'select',
        'validator' => 'opt',
        'viewer' => 'opt',
    ],
    'serial' => [
        'backend' => 'serial',
        'frontend' => 'int',
        'auto' => true,
    ],
    'tel' => [
        'backend' => 'varchar',
        'frontend' => 'tel',
        'validator' => 'text',
        'viewer' => 'tel',
    ],
    'text' => [
        'backend' => 'varchar',
        'frontend' => 'text',
        'validator' => 'text',
    ],
    'textarea' => [
        'backend' => 'text',
        'frontend' => 'textarea',
        'filter' => 'text',
        'validator' => 'text',
    ],
    'time' => [
        'backend' => 'time',
        'frontend' => 'time',
        'validator' => 'time',
        'viewer' => 'time',
    ],
    'uid' => [
        'backend' => 'varchar',
        'frontend' => 'text',
        'validator' => 'uid',
    ],
    'url' => [
        'backend' => 'varchar',
        'frontend' => 'url',
        'validator' => 'url',
        'viewer' => 'url',
    ],
    'video' => [
        'backend' => 'varchar',
        'frontend' => 'file',
        'filter' => 'text',
        'validator' => 'file',
        'viewer' => 'video',
        'ignorable' => true,
        'uploadable' => true,
        'accept' => ['video/mp4', 'video/ogg', 'video/webm'],
    ],
];
