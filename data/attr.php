<?php
return [
    'backend' => [
        'bool' => [
            'db_type' => 'boolean',
            'pdo' => PDO::PARAM_BOOL,
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
            'loader' => 'json',
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
            'editor' => 'opt',
            'viewer' => 'opt',
        ],
        'color' => [
            'validator' => 'color',
            'editor' => 'text',
        ],
        'date' => [
            'validator' => 'date',
            'editor' => 'date',
            'viewer' => 'date',
        ],
        'datetime' => [
            'validator' => 'datetime',
            'editor' => 'datetime',
            'viewer' => 'datetime',
        ],
        'email' => [
            'validator' => 'email',
            'editor' => 'text',
        ],
        'file' => [
            'validator' => 'file',
            'saver' => 'file',
            'editor' => 'file',
            'viewer' => 'file',
        ],
        'number' => [
            'editor' => 'int',
        ],
        'password' => [
            'validator' => 'text',
            'saver' => 'password',
            'editor' => 'password',
        ],
        'radio' => [
            'validator' => 'opt',
            'editor' => 'opt',
            'viewer' => 'opt',
        ],
        'range' => [
            'editor' => 'int',
        ],
        'select' => [
            'validator' => 'opt',
            'editor' => 'select',
            'viewer' => 'opt',
        ],
        'text' => [
            'validator' => 'text',
            'editor' => 'text',
        ],
        'textarea' => [
            'validator' => 'text',
            'editor' => 'textarea',
        ],
        'time' => [
            'validator' => 'time',
            'editor' => 'time',
            'viewer' => 'time',
        ],
        'url' => [
            'validator' => 'url',
            'editor' => 'text',
        ],
    ],
    'type' => [
        'audio' => [
            'name' => 'Audio',
            'backend' => 'varchar',
            'frontend' => 'file',
            'viewer' => 'audio',
        ],
        'checkbox' => [
            'name' => 'Checkbox',
            'backend' => 'bool',
            'frontend' => 'checkbox',
        ],
        'color' => [
            'name' => 'Color',
            'backend' => 'varchar',
            'frontend' => 'color',
        ],
        'date' => [
            'name' => 'Date',
            'backend' => 'date',
            'frontend' => 'date',
        ],
        'datetime' => [
            'name' => 'Datetime',
            'backend' => 'datetime',
            'frontend' => 'datetime',
        ],
        'decimal' => [
            'name' => 'Decimal',
            'backend' => 'decimal',
            'frontend' => 'number',
        ],
        'email' => [
            'name' => 'Email',
            'backend' => 'varchar',
            'frontend' => 'email',
        ],
        'embed' => [
            'name' => 'Embed',
            'backend' => 'varchar',
            'frontend' => 'file',
            'viewer' => 'embed',
        ],
        'entity' => [
            'name' => 'Entity',
            'backend' => 'int',
            'frontend' => 'select',
        ],
        'file' => [
            'name' => 'File',
            'backend' => 'varchar',
            'frontend' => 'file',
        ],
        'iframe' => [
            'name' => 'Iframe',
            'backend' => 'varchar',
            'frontend' => 'url',
            'viewer' => 'iframe',
        ],
        'image' => [
            'name' => 'Image',
            'backend' => 'varchar',
            'frontend' => 'file',
            'viewer' => 'image',
        ],
        'int' => [
            'name' => 'Integer',
            'backend' => 'int',
            'frontend' => 'number',
        ],
        'json' => [
            'name' => 'JSON',
            'backend' => 'json',
            'frontend' => 'textarea',
            'val' => [],
            'editor' => 'json',
            'validator' => 'json',
        ],
        'multicheckbox' => [
            'name' => 'Multicheckbox',
            'backend' => 'json',
            'frontend' => 'checkbox',
            'multiple' => true,
            'val' => [],
        ],
        'multiselect' => [
            'name' => 'Multiselect',
            'backend' => 'json',
            'frontend' => 'select',
            'multiple' => true,
            'val' => [],
        ],
        'object' => [
            'name' => 'Object',
            'backend' => 'varchar',
            'frontend' => 'file',
            'viewer' => 'object',
        ],
        'password' => [
            'name' => 'Password',
            'backend' => 'varchar',
            'frontend' => 'password',
        ],
        'radio' => [
            'name' => 'Radio',
            'backend' => 'varchar',
            'frontend' => 'radio',
        ],
        'range' => [
            'name' => 'Range',
            'backend' => 'int',
            'frontend' => 'range',
        ],
        'rte' => [
            'name' => 'Rich Text Editor',
            'backend' => 'text',
            'frontend' => 'textarea',
            'viewer' => 'rte',
            'validator' => 'rte',
        ],
        'search' => [
            'name' => 'Search',
            'backend' => 'search',
            'frontend' => 'textarea',
        ],
        'select' => [
            'name' => 'Select',
            'backend' => 'varchar',
            'frontend' => 'select',
        ],
        'text' => [
            'name' => 'Text',
            'backend' => 'varchar',
            'frontend' => 'text',
        ],
        'textarea' => [
            'name' => 'Textarea',
            'backend' => 'text',
            'frontend' => 'textarea',
        ],
        'time' => [
            'name' => 'Time',
            'backend' => 'time',
            'frontend' => 'time',
        ],
        'url' => [
            'name' => 'URL',
            'backend' => 'varchar',
            'frontend' => 'url',
        ],
        'video' => [
            'name' => 'Video',
            'backend' => 'varchar',
            'frontend' => 'file',
            'viewer' => 'video',
        ],
    ],
];
