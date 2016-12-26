<?php
return [
    'dsn' => 'mysql:host=mysql;dbname=qnd;charset=utf8',
    'user' => 'root',
    'password' => 'root',
    'driver_options' => [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ],
];
