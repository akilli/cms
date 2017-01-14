<?php
return [
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
];
