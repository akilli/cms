<?php
return [
    'driver' => 'mysql',
    'host' => 'mysql',
    'db' => 'qnd',
    'charset' => 'utf8',
    'user' => 'root',
    'password' => '',
    'driver_options' => [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ],
];
