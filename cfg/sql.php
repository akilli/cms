<?php
return [
    'dsn' => 'pgsql:host=cms-data;dbname=app',
    'user' => 'app',
    'password' => 'app',
    'opt' => [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ],
];
