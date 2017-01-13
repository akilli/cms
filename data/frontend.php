<?php
return [
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
        'deleter' => 'file',
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
];
