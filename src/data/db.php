<?php
return [
    'sql' => [
        'id' => 'sql',
        'callback' => 'akilli\sql_factory',
        'transaction' => 'akilli\sql_transaction',
        'driver' => 'mysql',
        'host' => 'mysql',
        'dbname' => 'akilli',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'driver_options' => [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ],
    ],
];
