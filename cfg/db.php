<?php
return [
    /**
     * @uses pdo\size()
     * @uses pdo\one()
     * @uses pdo\all()
     * @uses pdo\save()
     * @uses pdo\delete()
     * @uses pdo\transaction()
     */
    'app' => ['type' => 'pdo', 'dsn' => 'pgsql:host=db;dbname=app', 'user' => 'app', 'password' => 'app'],
];
