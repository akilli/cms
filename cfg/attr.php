<?php
return [
    'bool' => [
        'backend' => 'bool',
        'frontend' => 'frontend\bool',
        'viewer' => 'viewer\bool',
    ],
    'checkbox' => [
        'backend' => 'json',
        'frontend' => 'frontend\checkbox',
        'multiple' => true,
        'filter' => 'filter\opt',
        'viewer' => 'viewer\opt',
    ],
    'date' => [
        'backend' => 'date',
        'frontend' => 'frontend\date',
        'filter' => 'filter\date',
        'viewer' => 'viewer\date',
    ],
    'datetime' => [
        'backend' => 'datetime',
        'frontend' => 'frontend\datetime',
        'filter' => 'filter\datetime',
        'viewer' => 'viewer\datetime',
    ],
    'decimal' => [
        'backend' => 'decimal',
        'frontend' => 'frontend\number',
    ],
    'email' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\email',
        'filter' => 'filter\email',
    ],
    'ent' => [
        'backend' => 'int',
        'frontend' => 'frontend\select',
        'opt' => 'opt\ent',
        'filter' => 'filter\opt',
        'viewer' => 'viewer\opt',
    ],
    'file' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\file',
        'ignorable' => true,
        'filter' => 'filter\file',
        'viewer' => 'viewer\file',
    ],
    'int' => [
        'backend' => 'int',
        'frontend' => 'frontend\number',
    ],
    'json' => [
        'backend' => 'json',
        'frontend' => 'frontend\json',
        'viewer' => 'viewer\json',
    ],
    'password' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\password',
        'ignorable' => true,
    ],
    'radio' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\radio',
        'filter' => 'filter\opt',
        'viewer' => 'viewer\opt',
    ],
    'range' => [
        'backend' => 'int',
        'frontend' => 'frontend\range',
    ],
    'rte' => [
        'backend' => 'text',
        'frontend' => 'frontend\textarea',
        'viewer' => 'viewer\rte',
        'filter' => 'filter\rte',
    ],
    'select' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\select',
        'filter' => 'filter\opt',
        'viewer' => 'viewer\opt',
    ],
    'status' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\radio',
        'filter' => 'filter\opt',
        'viewer' => 'viewer\opt',
        'opt' => 'opt\status',
    ],
    'text' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\text',
        'filter' => 'filter\text',
    ],
    'textarea' => [
        'backend' => 'text',
        'frontend' => 'frontend\textarea',
        'filter' => 'filter\text',
    ],
    'time' => [
        'backend' => 'time',
        'frontend' => 'frontend\time',
        'filter' => 'filter\time',
        'viewer' => 'viewer\time',
    ],
    'url' => [
        'backend' => 'varchar',
        'frontend' => 'frontend\url',
        'filter' => 'filter\url',
    ],
];
