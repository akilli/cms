<?php
declare(strict_types=1);

namespace frontend;

return [
    'bool' => [
        'call' => bool(...),
    ],
    'browser' => [
        'call' => browser(...),
    ],
    'checkbox' => [
        'call' => checkbox(...),
    ],
    'date' => [
        'call' => date(...),
    ],
    'datetime' => [
        'call' => datetime(...),
    ],
    'decimal' => [
        'call' => decimal(...),
    ],
    'editor' => [
        'call' => editor(...),
    ],
    'email' => [
        'call' => email(...),
    ],
    'file' => [
        'call' => file(...),
    ],
    'int' => [
        'call' => int(...),
    ],
    'json' => [
        'call' => json(...),
    ],
    'password' => [
        'call' => password(...),
    ],
    'radio' => [
        'call' => radio(...),
    ],
    'range' => [
        'call' => range(...),
    ],
    'select' => [
        'call' => select(...),
    ],
    'tel' => [
        'call' => tel(...),
    ],
    'text' => [
        'call' => text(...),
    ],
    'textarea' => [
        'call' => textarea(...),
    ],
    'time' => [
        'call' => time(...),
    ],
    'url' => [
        'call' => url(...),
    ],
];
