<?php
declare(strict_types=1);

return [
    'app' => [
        'dsn' => 'pgsql:host=db;dbname=app',
        'user' => 'app',
        'password' => getenv('APP_DB_PASSWORD'),
    ],
];
