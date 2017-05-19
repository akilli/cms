<?php
return [
    'default' => [
        'id' => null,
        'name' => null,
        'col' => null,
        'auto' => false,
        'sort' => 0,
        'type' => null,
        'backend' => null,
        'frontend' => null,
        'db_type' => null,
        'pdo' => null,
        'nullable' => false,
        'required' => false,
        'uniq' => false,
        'multiple' => false,
        'searchable' => false,
        'opt' => [],
        'actions' => [],
        'val' => null,
        'minval' => 0,
        'maxval' => 0,
        'entity' => null,
        'context' => null,
        'html' => [],
        'validator' => null,
        'saver' => null,
        'loader' => null,
        'editor' => null,
        'viewer' => null,
    ],
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
            'validator' => 'qnd\validator_opt',
            'editor' => 'qnd\editor_opt',
            'viewer' => 'qnd\viewer_opt',
        ],
        'date' => [
            'validator' => 'qnd\validator_date',
            'editor' => 'qnd\editor_date',
            'viewer' => 'qnd\viewer_date',
        ],
        'datetime' => [
            'validator' => 'qnd\validator_datetime',
            'editor' => 'qnd\editor_datetime',
            'viewer' => 'qnd\viewer_datetime',
        ],
        'email' => [
            'validator' => 'qnd\validator_email',
            'editor' => 'qnd\editor_text',
        ],
        'file' => [
            'validator' => 'qnd\validator_file',
            'saver' => 'qnd\saver_file',
            'editor' => 'qnd\editor_file',
            'viewer' => 'qnd\viewer_file',
        ],
        'number' => [
            'editor' => 'qnd\editor_int',
        ],
        'password' => [
            'validator' => 'qnd\validator_text',
            'saver' => 'qnd\saver_password',
            'editor' => 'qnd\editor_password',
        ],
        'radio' => [
            'validator' => 'qnd\validator_opt',
            'editor' => 'qnd\editor_opt',
            'viewer' => 'qnd\viewer_opt',
        ],
        'range' => [
            'editor' => 'qnd\editor_int',
        ],
        'select' => [
            'validator' => 'qnd\validator_opt',
            'editor' => 'qnd\editor_select',
            'viewer' => 'qnd\viewer_opt',
        ],
        'text' => [
            'validator' => 'qnd\validator_text',
            'editor' => 'qnd\editor_text',
        ],
        'textarea' => [
            'validator' => 'qnd\validator_text',
            'editor' => 'qnd\editor_textarea',
        ],
        'time' => [
            'validator' => 'qnd\validator_time',
            'editor' => 'qnd\editor_time',
            'viewer' => 'qnd\viewer_time',
        ],
        'url' => [
            'validator' => 'qnd\validator_url',
            'editor' => 'qnd\editor_text',
        ],
    ],
    'type' => [
        'audio' => [
            'backend' => 'varchar',
            'frontend' => 'file',
            'viewer' => 'qnd\viewer_audio',
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
            'viewer' => 'qnd\viewer_image',
        ],
        'int' => [
            'backend' => 'int',
            'frontend' => 'number',
        ],
        'json' => [
            'backend' => 'json',
            'frontend' => 'textarea',
            'editor' => 'qnd\editor_json',
            'validator' => 'qnd\validator_json',
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
            'viewer' => 'qnd\viewer_rte',
            'validator' => 'qnd\validator_rte',
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
            'viewer' => 'qnd\viewer_video',
        ],
    ],
];
