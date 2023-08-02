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
    'entitychild' => [
        'call' => entitychild(...),
    ],
    'privilege' => [
        'call' => privilege(...),
    ],
];
