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
            'multiple' => true,
            'val' => [],
            'loader' => 'loader\json',
        ],
        'text' => [],
        'time' => [],
        'varchar' => [],
    ],
    'frontend' => [
        'checkbox' => [
            'validator' => 'validator\opt',
            'viewer' => 'viewer\opt',
        ],
        'date' => [
            'validator' => 'validator\date',
            'viewer' => 'viewer\date',
        ],
        'datetime' => [
            'validator' => 'validator\datetime',
            'viewer' => 'viewer\datetime',
        ],
        'email' => [
            'validator' => 'validator\email',
        ],
        'file' => [
            'validator' => 'validator\file',
            'viewer' => 'viewer\file',
        ],
        'number' => [],
        'password' => [
            'validator' => 'validator\password',
        ],
        'radio' => [
            'validator' => 'validator\opt',
            'viewer' => 'viewer\opt',
        ],
        'range' => [],
        'select' => [
            'validator' => 'validator\opt',
            'viewer' => 'viewer\opt',
        ],
        'text' => [
            'validator' => 'validator\text',
        ],
        'textarea' => [
            'validator' => 'validator\text',
        ],
        'time' => [
            'validator' => 'validator\time',
            'viewer' => 'viewer\time',
        ],
        'url' => [
            'validator' => 'validator\url',
        ],
    ],
    'type' => [
        'audio' => [
            'backend' => 'varchar',
            'frontend' => 'file',
            'validator' => 'validator\audio',
            'viewer' => 'viewer\audio',
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
            'validator' => 'validator\embed',
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
            'validator' => 'validator\image',
            'viewer' => 'viewer\image',
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
            'viewer' => 'viewer\rte',
            'validator' => 'validator\rte',
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
            'validator' => 'validator\video',
            'viewer' => 'viewer\video',
        ],
    ],
];
