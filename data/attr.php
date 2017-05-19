<?php
return [
    'backend' => [
        'bool' => [
            'db_type' => 'boolean',
            'pdo' => PDO::PARAM_BOOL,
            'val' => false,
        ],
        'date' => [
            'db_type' => 'date',
            'pdo' => PDO::PARAM_STR,
        ],
        'datetime' => [
            'db_type' => 'timestamp',
            'pdo' => PDO::PARAM_STR,
        ],
        'decimal' => [
            'db_type' => 'decimal',
            'pdo' => PDO::PARAM_INT,
        ],
        'int' => [
            'db_type' => 'integer',
            'pdo' => PDO::PARAM_INT,
        ],
        'json' => [
            'db_type' => 'jsonb',
            'pdo' => PDO::PARAM_STR,
            'val' => [],
            'loader' => 'qnd\loader_json',
        ],
        'search' => [
            'db_type' => 'tsvector',
            'pdo' => PDO::PARAM_STR,
        ],
        'text' => [
            'db_type' => 'text',
            'pdo' => PDO::PARAM_STR,
        ],
        'time' => [
            'db_type' => 'time',
            'pdo' => PDO::PARAM_STR,
        ],
        'varchar' => [
            'db_type' => 'varchar',
            'pdo' => PDO::PARAM_STR,
        ],
    ],
    'frontend' => [
        'checkbox' => [
            'validator' => 'opt',
            'editor' => 'qnd\editor_opt',
            'viewer' => 'opt',
        ],
        'date' => [
            'validator' => 'date',
            'editor' => 'qnd\editor_date',
            'viewer' => 'date',
        ],
        'datetime' => [
            'validator' => 'datetime',
            'editor' => 'qnd\editor_datetime',
            'viewer' => 'datetime',
        ],
        'email' => [
            'validator' => 'email',
            'editor' => 'qnd\editor_text',
        ],
        'file' => [
            'validator' => 'file',
            'saver' => 'file',
            'editor' => 'qnd\editor_file',
            'viewer' => 'file',
        ],
        'number' => [
            'editor' => 'qnd\editor_int',
        ],
        'password' => [
            'validator' => 'text',
            'saver' => 'password',
            'editor' => 'qnd\editor_password',
        ],
        'radio' => [
            'validator' => 'opt',
            'editor' => 'qnd\editor_opt',
            'viewer' => 'opt',
        ],
        'range' => [
            'editor' => 'qnd\editor_int',
        ],
        'select' => [
            'validator' => 'opt',
            'editor' => 'qnd\editor_select',
            'viewer' => 'opt',
        ],
        'text' => [
            'validator' => 'text',
            'editor' => 'qnd\editor_text',
        ],
        'textarea' => [
            'validator' => 'text',
            'editor' => 'qnd\editor_textarea',
        ],
        'time' => [
            'validator' => 'time',
            'editor' => 'qnd\editor_time',
            'viewer' => 'time',
        ],
        'url' => [
            'validator' => 'url',
            'editor' => 'qnd\editor_text',
        ],
    ],
    'type' => [
        'audio' => [
            'backend' => 'varchar',
            'frontend' => 'file',
            'viewer' => 'audio',
        ],
        'checkbox' => [
            'backend' => 'bool',
            'frontend' => 'checkbox',
        ],
        'date' => [
            'backend' => 'date',
            'frontend' => 'date',
        ],
        'datetime' => [
            'backend' => 'datetime',
            'frontend' => 'datetime',
        ],
        'decimal' => [
            'backend' => 'decimal',
            'frontend' => 'number',
        ],
        'email' => [
            'backend' => 'varchar',
            'frontend' => 'email',
        ],
        'entity' => [
            'backend' => 'int',
            'frontend' => 'select',
        ],
        'file' => [
            'backend' => 'varchar',
            'frontend' => 'file',
        ],
        'image' => [
            'backend' => 'varchar',
            'frontend' => 'file',
            'viewer' => 'image',
        ],
        'int' => [
            'backend' => 'int',
            'frontend' => 'number',
        ],
        'json' => [
            'backend' => 'json',
            'frontend' => 'textarea',
            'editor' => 'qnd\editor_json',
            'validator' => 'json',
        ],
        'password' => [
            'backend' => 'varchar',
            'frontend' => 'password',
        ],
        'radio' => [
            'backend' => 'varchar',
            'frontend' => 'radio',
        ],
        'range' => [
            'backend' => 'int',
            'frontend' => 'range',
        ],
        'rte' => [
            'backend' => 'text',
            'frontend' => 'textarea',
            'viewer' => 'rte',
            'validator' => 'rte',
        ],
        'search' => [
            'backend' => 'search',
            'frontend' => 'textarea',
        ],
        'select' => [
            'backend' => 'varchar',
            'frontend' => 'select',
        ],
        'text' => [
            'backend' => 'varchar',
            'frontend' => 'text',
        ],
        'textarea' => [
            'backend' => 'text',
            'frontend' => 'textarea',
        ],
        'time' => [
            'backend' => 'time',
            'frontend' => 'time',
        ],
        'url' => [
            'backend' => 'varchar',
            'frontend' => 'url',
        ],
        'video' => [
            'backend' => 'varchar',
            'frontend' => 'file',
            'viewer' => 'video',
        ],
    ],
];
