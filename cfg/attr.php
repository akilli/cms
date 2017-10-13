<?php
return [
    'backend' => [
        'bool' => [
            'val' => false,
        ],
        'date' => [],
        'datetime' => [],
        'decimal' => [],
        'int' => [],
        'json' => [
            'val' => [],
            'loader' => 'loader\json',
        ],
        'search' => [],
        'text' => [],
        'time' => [],
        'varchar' => [],
    ],
    'frontend' => [
        'checkbox' => [
            'validator' => 'validator\opt',
            'editor' => 'editor\opt',
            'viewer' => 'viewer\opt',
        ],
        'date' => [
            'validator' => 'validator\date',
            'editor' => 'editor\date',
            'viewer' => 'viewer\date',
        ],
        'datetime' => [
            'validator' => 'validator\datetime',
            'editor' => 'editor\datetime',
            'viewer' => 'viewer\datetime',
        ],
        'email' => [
            'validator' => 'validator\email',
            'editor' => 'editor\text',
        ],
        'file' => [
            'validator' => 'validator\file',
            'editor' => 'editor\file',
            'viewer' => 'viewer\file',
        ],
        'number' => [
            'editor' => 'editor\int',
        ],
        'password' => [
            'validator' => 'validator\password',
            'editor' => 'editor\password',
        ],
        'radio' => [
            'validator' => 'validator\opt',
            'editor' => 'editor\opt',
            'viewer' => 'viewer\opt',
        ],
        'range' => [
            'editor' => 'editor\int',
        ],
        'select' => [
            'validator' => 'validator\opt',
            'editor' => 'editor\select',
            'viewer' => 'viewer\opt',
        ],
        'text' => [
            'validator' => 'validator\text',
            'editor' => 'editor\text',
        ],
        'textarea' => [
            'validator' => 'validator\text',
            'editor' => 'editor\textarea',
        ],
        'time' => [
            'validator' => 'validator\time',
            'editor' => 'editor\time',
            'viewer' => 'viewer\time',
        ],
        'url' => [
            'validator' => 'validator\url',
            'editor' => 'editor\text',
        ],
    ],
    'type' => [
        'audio' => [
            'backend' => 'varchar',
            'frontend' => 'file',
            'viewer' => 'viewer\audio',
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
            'viewer' => 'viewer\embed',
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
            'viewer' => 'viewer\iframe',
        ],
        'image' => [
            'backend' => 'varchar',
            'frontend' => 'file',
            'viewer' => 'viewer\image',
        ],
        'int' => [
            'backend' => 'int',
            'frontend' => 'number',
        ],
        'json' => [
            'backend' => 'json',
            'frontend' => 'textarea',
            'editor' => 'editor\json',
            'validator' => 'validator\json',
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
            'viewer' => 'viewer\rte',
            'validator' => 'validator\rte',
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
            'viewer' => 'viewer\video',
        ],
    ],
];
