<?php
return [
    'driver' => 'pgsql',
    'host' => 'postgres',
    'db' => 'qnd',
    'user' => 'postgres',
    'password' => 'postgres',
    'charset' => 'utf8',
    'driver_options' => [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ],
];
