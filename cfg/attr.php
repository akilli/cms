<?php
return [
    'backend' => [
        'bool' => [],
        'date' => [],
        'datetime' => [],
        'decimal' => [],
        'int' => [],
        'json' => [
            'multiple' => true,
            'val' => [],
        ],
        'text' => [],
        'time' => [],
        'varchar' => [],
    ],
    'frontend' => [
        'checkbox' => [
            'validator' => 'opt',
            'viewer' => 'opt',
        ],
        'date' => [
            'validator' => 'date',
            'viewer' => 'date',
        ],
        'datetime' => [
            'validator' => 'datetime',
            'viewer' => 'datetime',
        ],
        'email' => [
            'validator' => 'email',
        ],
        'file' => [
            'validator' => 'file',
            'viewer' => 'file',
        ],
        'number' => [],
        'password' => [
            'validator' => 'password',
        ],
        'radio' => [
            'validator' => 'opt',
            'viewer' => 'opt',
        ],
        'range' => [],
        'select' => [
            'validator' => 'opt',
            'viewer' => 'opt',
        ],
        'text' => [
            'validator' => 'text',
        ],
        'textarea' => [
            'validator' => 'text',
        ],
        'time' => [
            'validator' => 'time',
            'viewer' => 'time',
        ],
        'url' => [
            'validator' => 'url',
        ],
    ],
    'type' => [
        'audio' => [
            'backend' => 'varchar',
            'frontend' => 'file',
            'validator' => 'audio',
            'viewer' => 'audio',
        ],
        'bool' => [
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
            'validator' => 'embed',
            'viewer' => 'embed',
        ],
        'ent' => [
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
            'viewer' => 'iframe',
        ],
        'image' => [
            'backend' => 'varchar',
            'frontend' => 'file',
            'validator' => 'image',
            'viewer' => 'image',
        ],
        'int' => [
            'backend' => 'int',
            'frontend' => 'number',
        ],
        'json' => [
            'backend' => 'json',
            'frontend' => 'checkbox',
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
            'validator' => 'video',
            'viewer' => 'video',
        ],
    ],
];
