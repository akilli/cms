<?php
return [
    'bool' => [
        'backend' => 'bool',
        'frontend' => 'bool',
        'viewer' => 'bool',
    ],
    'date' => [
        'backend' => 'date',
        'frontend' => 'date',
        'filter' => 'date',
        'viewer' => 'date',
    ],
    'datetime' => [
        'backend' => 'datetime',
        'frontend' => 'datetime',
        'filter' => 'datetime',
        'viewer' => 'datetime',
    ],
    'decimal' => [
        'backend' => 'decimal',
        'frontend' => 'number',
    ],
    'email' => [
        'backend' => 'varchar',
        'frontend' => 'email',
        'filter' => 'email',
    ],
    'ent' => [
        'backend' => 'int',
        'frontend' => 'select',
        'filter' => 'opt',
        'viewer' => 'opt',
    ],
    'file' => [
        'backend' => 'varchar',
        'frontend' => 'file',
        'ignorable' => true,
        'filter' => 'file',
        'viewer' => 'file',
    ],
    'int' => [
        'backend' => 'int',
        'frontend' => 'number',
    ],
    'json' => [
        'backend' => 'json',
        'frontend' => 'checkbox',
        'multiple' => true,
        'filter' => 'opt',
        'viewer' => 'opt',
    ],
    'password' => [
        'backend' => 'varchar',
        'frontend' => 'password',
        'ignorable' => true,
    ],
    'radio' => [
        'backend' => 'varchar',
        'frontend' => 'radio',
        'filter' => 'opt',
        'viewer' => 'opt',
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
        'filter' => 'opt',
        'viewer' => 'opt',
    ],
    'text' => [
        'backend' => 'varchar',
        'frontend' => 'text',
        'filter' => 'text',
    ],
    'textarea' => [
        'backend' => 'text',
        'frontend' => 'textarea',
        'filter' => 'text',
    ],
    'time' => [
        'backend' => 'time',
        'frontend' => 'time',
        'filter' => 'time',
        'viewer' => 'time',
    ],
    'url' => [
        'backend' => 'varchar',
        'frontend' => 'url',
        'filter' => 'url',
    ],
];
