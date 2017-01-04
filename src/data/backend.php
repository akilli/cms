<?php
return [
    'mysql' => [
        'bool' => 'UNSIGNED',
        'date' => 'DATE',
        'datetime' => 'DATETIME',
        'decimal' => 'DECIMAL',
        'int' => 'SIGNED',
        'json' => 'JSON',
        'text' => 'CHAR',
        'time' => 'TIME',
        'varchar' => 'CHAR',
    ],
    'pgsql' => [
        'bool' => 'BOOLEAN',
        'date' => 'DATE',
        'datetime' => 'TIMESTAMP',
        'decimal' => 'DECIMAL',
        'int' => 'INTEGER',
        'json' => 'JSON',
        'text' => 'TEXT',
        'time' => 'TIME',
        'varchar' => 'VARCHAR',
    ],
];
