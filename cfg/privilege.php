<?php
declare(strict_types=1);

return [
    '_all_' => [
        'name' => 'All',
    ],
    '_guest_' => [
        'name' => 'Guest',
        'auto' => true,
    ],
    '_public_' => [
        'name' => 'Public',
        'auto' => true,
    ],
    '_user_' => [
        'name' => 'User',
        'auto' => true,
    ],
    'account:add' => [
        'use' => '_all_',
    ],
    'account:delete' => [
        'use' => '_all_',
    ],
    'account:edit' => [
        'use' => '_all_',
    ],
    'account:index' => [
        'use' => '_all_',
    ],
    'account:login' => [
        'use' => '_guest_',
    ],
    'account:logout' => [
        'use' => '_user_',
    ],
    'account:profile' => [
        'use' => '_user_',
    ],
    'account:view' => [
        'use' => '_user_',
    ],
    'block:api' => [
        'use' => 'block:index',
    ],
    'role:add' => [
        'use' => '_all_',
    ],
    'role:delete' => [
        'use' => '_all_',
    ],
    'role:edit' => [
        'use' => '_all_',
    ],
    'role:index' => [
        'use' => '_all_',
    ],
];
