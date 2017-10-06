<?php
return [
    'host' => 'cms-data',
    'db' => 'app',
    'user' => 'app',
    'password' => 'app',
    'opt' => [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ],
];
