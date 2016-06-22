<?php
return [
    'bool' => [
        'cast' => 'boolval',
        'db_cast' => 'UNSIGNED',
        'db_type' => PDO::PARAM_BOOL,
    ],
    'date' => [
        'cast' => 'strval',
        'db_cast' => 'DATE',
        'db_type' => PDO::PARAM_STR,
    ],
    'datetime' => [
        'cast' => 'strval',
        'db_cast' => 'DATETIME',
        'db_type' => PDO::PARAM_STR,
    ],
    'decimal' => [
        'cast' => 'floatval',
        'db_cast' => 'DECIMAL',
        'db_type' => PDO::PARAM_INT,
    ],
    'int' => [
        'cast' => 'intval',
        'db_cast' => 'SIGNED',
        'db_type' => PDO::PARAM_INT,
    ],
    'json' => [
        'cast' => 'strval',
        'db_cast' => 'JSON',
        'db_type' => PDO::PARAM_STR,
    ],
    'text' => [
        'cast' => 'strval',
        'db_cast' => 'CHAR',
        'db_type' => PDO::PARAM_STR,
    ],
    'time' => [
        'cast' => 'strval',
        'db_cast' => 'TIME',
        'db_type' => PDO::PARAM_STR,
    ],
    'varchar' => [
        'cast' => 'strval',
        'db_cast' => 'CHAR',
        'db_type' => PDO::PARAM_STR,
    ],
];
