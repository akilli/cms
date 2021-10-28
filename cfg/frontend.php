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
    'editor' => [
        'call' => editor(...),
    ],
    'email' => [
        'call' => email(...),
    ],
    'file' => [
        'call' => file(...),
    ],
    'json' => [
        'call' => json(...),
    ],
    'number' => [
        'call' => number(...),
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
