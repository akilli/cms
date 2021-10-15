<?php
declare(strict_types=1);

namespace opt;

return [
    'block' => [
        'call' => block(...),
    ],
    'bool' => [
        'call' => bool(...),
    ],
    'entity' => [
        'call' => entity(...),
    ],
    'entitychild' => [
        'call' => entitychild(...),
    ],
    'privilege' => [
        'call' => privilege(...),
    ],
];
