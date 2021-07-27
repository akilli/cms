<?php
return [
    '_all_' => ['name' => 'ALL PRIVILEGES'],
    '_guest_' => ['name' => 'GUEST', 'auto' => true],
    '_public_' => ['name' => 'PUBLIC', 'auto' => true],
    '_user_' => ['name' => 'USER', 'auto' => true],
    'account:dashboard' => ['use' => '_user_'],
    'account:delete' => ['use' => '_all_'],
    'account:edit' => ['use' => '_all_'],
    'account:index' => ['use' => '_all_'],
    'account:login' => ['use' => '_guest_'],
    'account:logout' => ['use' => '_user_'],
    'account:profile' => ['use' => '_user_'],
    'block:api' => ['use' => 'block:index'],
    'role:delete' => ['use' => '_all_'],
    'role:edit' => ['use' => '_all_'],
    'role:index' => ['use' => '_all_'],
];
