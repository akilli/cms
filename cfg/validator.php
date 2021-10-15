<?php
declare(strict_types=1);

namespace validator;

return [
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
    'entity' => [
        'call' => entity(...),
    ],
    'multientity' => [
        'call' => multientity(...),
    ],
    'opt' => [
        'call' => opt(...),
    ],
    'text' => [
        'call' => text(...),
    ],
    'time' => [
        'call' => time(...),
    ],
    'uid' => [
        'call' => uid(...),
    ],
    'url' => [
        'call' => url(...),
    ],
    'urlpath' => [
        'call' => urlpath(...),
    ],
];
