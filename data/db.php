<?php
return [
    'host' => 'postgres',
    'db' => 'qnd',
    'user' => 'postgres',
    'password' => 'postgres',
    'opt' => [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ],
];
