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
            'saver' => 'cms\saver_json',
            'loader' => 'cms\loader_json',
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
            'validator' => 'cms\validator_opt',
            'editor' => 'cms\editor_checkbox',
            'viewer' => 'cms\viewer_opt',
        ],
        'date' => [
            'validator' => 'cms\validator_date',
            'editor' => 'cms\editor_date',
            'viewer' => 'cms\viewer_date',
        ],
        'datetime' => [
            'validator' => 'cms\validator_datetime',
            'editor' => 'cms\editor_datetime',
            'viewer' => 'cms\viewer_datetime',
        ],
        'email' => [
            'validator' => 'cms\validator_email',
            'editor' => 'cms\editor_text',
        ],
        'file' => [
            'validator' => 'cms\validator_file',
            'saver' => 'cms\saver_file',
            'editor' => 'cms\editor_file',
            'viewer' => 'cms\viewer_file',
        ],
        'number' => [
            'editor' => 'cms\editor_int',
        ],
        'password' => [
            'validator' => 'cms\validator_password',
            'editor' => 'cms\editor_password',
        ],
        'radio' => [
            'validator' => 'cms\validator_opt',
            'editor' => 'cms\editor_radio',
            'viewer' => 'cms\viewer_opt',
        ],
        'range' => [
            'editor' => 'cms\editor_int',
        ],
        'select' => [
            'validator' => 'cms\validator_opt',
            'editor' => 'cms\editor_select',
            'viewer' => 'cms\viewer_opt',
        ],
        'text' => [
            'validator' => 'cms\validator_text',
            'editor' => 'cms\editor_text',
        ],
        'textarea' => [
            'validator' => 'cms\validator_text',
            'editor' => 'cms\editor_textarea',
        ],
        'time' => [
            'validator' => 'cms\validator_time',
            'editor' => 'cms\editor_time',
            'viewer' => 'cms\viewer_time',
        ],
        'url' => [
            'validator' => 'cms\validator_url',
            'editor' => 'cms\editor_text',
        ],
    ],
    'type' => [
        'audio' => [
            'backend' => 'varchar',
            'frontend' => 'file',
            'viewer' => 'cms\viewer_audio',
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
        'embed' => [
            'backend' => 'varchar',
            'frontend' => 'file',
            'viewer' => 'cms\viewer_embed',
        ],
        'entity' => [
            'backend' => 'int',
            'frontend' => 'select',
        ],
        'file' => [
            'backend' => 'varchar',
            'frontend' => 'file',
        ],
        'iframe' => [
            'backend' => 'varchar',
            'frontend' => 'url',
            'viewer' => 'cms\viewer_iframe',
        ],
        'image' => [
            'backend' => 'varchar',
            'frontend' => 'file',
            'viewer' => 'cms\viewer_image',
        ],
        'int' => [
            'backend' => 'int',
            'frontend' => 'number',
        ],
        'json' => [
            'backend' => 'json',
            'frontend' => 'textarea',
            'editor' => 'cms\editor_json',
            'validator' => 'cms\validator_json',
        ],
        'object' => [
            'backend' => 'varchar',
            'frontend' => 'file',
            'viewer' => 'cms\viewer_object',
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
            'viewer' => 'cms\viewer_rte',
            'validator' => 'cms\validator_rte',
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
            'viewer' => 'cms\viewer_video',
        ],
    ],
];
