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
        ],
        'text' => [],
        'time' => [],
        'varchar' => [],
    ],
    'frontend' => [
        'checkbox' => [
            'multiple' => true,
            'filter' => 'opt',
            'viewer' => 'opt',
        ],
        'date' => [
            'filter' => 'date',
            'viewer' => 'date',
        ],
        'datetime' => [
            'filter' => 'datetime',
            'viewer' => 'datetime',
        ],
        'email' => [
            'filter' => 'email',
        ],
        'file' => [
            'filter' => 'file',
            'viewer' => 'file',
        ],
        'number' => [],
        'password' => [],
        'radio' => [
            'filter' => 'opt',
            'viewer' => 'opt',
        ],
        'range' => [],
        'select' => [
            'filter' => 'opt',
            'viewer' => 'opt',
        ],
        'text' => [
            'filter' => 'text',
        ],
        'textarea' => [
            'filter' => 'text',
        ],
        'time' => [
            'filter' => 'time',
            'viewer' => 'time',
        ],
        'toggle' => [
            'viewer' => 'opt',
        ],
        'url' => [
            'filter' => 'url',
        ],
    ],
    'type' => [
        'audio' => [
            'backend' => 'varchar',
            'frontend' => 'file',
            'filter' => 'audio',
            'viewer' => 'audio',
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
            'filter' => 'image',
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
            'filter' => 'rte',
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
        'toggle' => [
            'backend' => 'bool',
            'frontend' => 'toggle',
        ],
        'url' => [
            'backend' => 'varchar',
            'frontend' => 'url',
        ],
        'video' => [
            'backend' => 'varchar',
            'frontend' => 'file',
            'filter' => 'video',
            'viewer' => 'video',
        ],
    ],
];
