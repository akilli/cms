<?php
declare(strict_types=1);

namespace opt;

return [
    'bool' => [
        'call' => bool(...),
    ],
    'entity' => [
        'call' => entity(...),
    ],
    'privilege' => [
        'call' => privilege(...),
    ],
];
