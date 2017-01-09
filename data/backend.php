<?php
return [
    'bool' => [
        'db' => 'boolean',
        'pdo' => PDO::PARAM_BOOL,
    ],
    'date' => [
        'db' => 'date',
        'pdo' => PDO::PARAM_STR,
    ],
    'datetime' => [
        'db' => 'timestamp',
        'pdo' => PDO::PARAM_STR,
    ],
    'decimal' => [
        'db' => 'decimal',
        'pdo' => PDO::PARAM_INT,
    ],
    'int' => [
        'db' => 'integer',
        'pdo' => PDO::PARAM_INT,
    ],
    'json' => [
        'db' => 'json',
        'pdo' => PDO::PARAM_STR,
    ],
    'search' => [
        'db' => 'tsvector',
        'pdo' => PDO::PARAM_STR,
    ],
    'text' => [
        'db' => 'text',
        'pdo' => PDO::PARAM_STR,
    ],
    'time' => [
        'db' => 'time',
        'pdo' => PDO::PARAM_STR,
    ],
    'varchar' => [
        'db' => 'varchar',
        'pdo' => PDO::PARAM_STR,
    ],
];
