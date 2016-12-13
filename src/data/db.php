<?php
return [
    'dsn' => 'mysql:host=mysql;dbname=qnd;user=root;password=;charset=utf8',
    'driver_options' => [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ],
];
