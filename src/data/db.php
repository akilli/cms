<?php
return [
    'sql' => [
        'id' => 'sql',
        'callback' => 'sql\factory',
        'transaction' => 'sql\transaction',
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
