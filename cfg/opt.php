<?php
declare(strict_types=1);

namespace opt;

return [
    'bool' => [
        'call' => bool(...),
    ],
    'container' => [
        'call' => container(...),
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
